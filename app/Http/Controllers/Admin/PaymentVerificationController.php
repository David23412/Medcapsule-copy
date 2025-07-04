<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display pending payments for admin verification
     */
    public function pendingPayments(Request $request)
    {
        $query = Payment::with(['user', 'course'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        // Filter by payment method if specified
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Filter by date range if specified
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by user name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(20);

        // Get statistics
        $stats = $this->getPaymentStats();

        return view('admin.payments.pending', compact('payments', 'stats'));
    }

    /**
     * Display all payments with filters
     */
    public function allPayments(Request $request)
    {
        $query = Payment::with(['user', 'course'])
            ->orderBy('created_at', 'desc');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Payment method filter
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(25);
        $stats = $this->getPaymentStats();

        return view('admin.payments.all', compact('payments', 'stats'));
    }

    /**
     * Show individual payment details
     */
    public function showPayment(Payment $payment)
    {
        $payment->load(['user', 'course']);
        
        // Get payment logs for audit trail
        $logs = PaymentLog::where('payment_id', $payment->id)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.payments.show', compact('payment', 'logs'));
    }

    /**
     * Approve a payment
     */
    public function approvePayment(Request $request, Payment $payment)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Check if payment is still pending
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is no longer pending and cannot be approved.'
                ], 400);
            }

            // Update payment status
            $oldStatus = $payment->status;
            $payment->status = 'completed';
            $payment->verified_by = Auth::id();
            $payment->verified_at = now();
            $payment->admin_notes = $request->admin_notes;
            $payment->save();

            // Enroll user in course
            if ($payment->course) {
                $payment->user->enrollInCourse($payment->course->id);
            }

            // Create payment log entry
            PaymentLog::create([
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'admin_id' => Auth::id(),
                'action' => 'admin_approved',
                'status_before' => $oldStatus,
                'status_after' => 'completed',
                'data' => [
                    'admin_notes' => $request->admin_notes,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'course_enrolled' => $payment->course ? $payment->course->name : null
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            // Log the action
            Log::info('Payment approved by admin', [
                'payment_id' => $payment->id,
                'admin_id' => Auth::id(),
                'user_id' => $payment->user_id,
                'course_id' => $payment->course_id,
                'amount' => $payment->amount
            ]);

            // TODO: Send email notification to user
            // $this->sendPaymentApprovalEmail($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully. User has been enrolled in the course.',
                'payment_status' => 'completed'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error approving payment', [
                'payment_id' => $payment->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a payment
     */
    public function rejectPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Check if payment is still pending
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is no longer pending and cannot be rejected.'
                ], 400);
            }

            // Update payment status
            $oldStatus = $payment->status;
            $payment->status = 'rejected';
            $payment->verified_by = Auth::id();
            $payment->verified_at = now();
            $payment->rejection_reason = $request->rejection_reason;
            $payment->admin_notes = $request->admin_notes;
            $payment->save();

            // Create payment log entry
            PaymentLog::create([
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'admin_id' => Auth::id(),
                'action' => 'admin_rejected',
                'status_before' => $oldStatus,
                'status_after' => 'rejected',
                'data' => [
                    'rejection_reason' => $request->rejection_reason,
                    'admin_notes' => $request->admin_notes,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            // Log the action
            Log::info('Payment rejected by admin', [
                'payment_id' => $payment->id,
                'admin_id' => Auth::id(),
                'user_id' => $payment->user_id,
                'rejection_reason' => $request->rejection_reason
            ]);

            // TODO: Send email notification to user
            // $this->sendPaymentRejectionEmail($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment rejected successfully.',
                'payment_status' => 'rejected'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error rejecting payment', [
                'payment_id' => $payment->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request more information from user
     */
    public function requestMoreInfo(Request $request, Payment $payment)
    {
        $request->validate([
            'info_request' => 'required|string|max:1000'
        ]);

        try {
            // Create payment log entry
            PaymentLog::create([
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'admin_id' => Auth::id(),
                'action' => 'info_requested',
                'status_before' => $payment->status,
                'status_after' => $payment->status,
                'data' => [
                    'info_request' => $request->info_request,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // TODO: Send email to user requesting more info
            // $this->sendInfoRequestEmail($payment, $request->info_request);

            return response()->json([
                'success' => true,
                'message' => 'Information request sent to user.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error requesting payment info', [
                'payment_id' => $payment->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send information request.'
            ], 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        $stats = [
            'pending' => Payment::where('status', 'pending')->count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'rejected' => Payment::where('status', 'rejected')->count(),
            'total_today' => Payment::whereDate('created_at', today())->count(),
            'total_amount_today' => Payment::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount'),
            'avg_verification_time' => Payment::where('status', '!=', 'pending')
                ->whereNotNull('verified_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, verified_at)) as avg_time')
                ->value('avg_time'),
        ];

        // Payment methods breakdown
        $methodStats = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $stats['methods'] = $methodStats;

        return $stats;
    }

    /**
     * Get receipt image for a payment
     */
    public function getReceiptImage(Payment $payment)
    {
        try {
            if (!$payment->receipt_path || !Storage::exists($payment->receipt_path)) {
                return response()->json(['error' => 'Receipt not found'], 404);
            }

            $receiptUrl = Storage::url($payment->receipt_path);
            
            return response()->json([
                'success' => true,
                'receipt_url' => $receiptUrl,
                'receipt_path' => $payment->receipt_path
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving receipt image', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to retrieve receipt'], 500);
        }
    }

    /**
     * Bulk approve payments
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $payments = Payment::whereIn('id', $request->payment_ids)
                ->where('status', 'pending')
                ->get();

            $approved = 0;
            $errors = [];

            foreach ($payments as $payment) {
                try {
                    // Approve payment
                    $payment->status = 'completed';
                    $payment->verified_by = Auth::id();
                    $payment->verified_at = now();
                    $payment->admin_notes = $request->admin_notes;
                    $payment->save();

                    // Enroll user in course
                    if ($payment->course) {
                        $payment->user->enrollInCourse($payment->course->id);
                    }

                    // Log the action
                    PaymentLog::create([
                        'payment_id' => $payment->id,
                        'user_id' => $payment->user_id,
                        'admin_id' => Auth::id(),
                        'action' => 'bulk_approved',
                        'status_before' => 'pending',
                        'status_after' => 'completed',
                        'data' => [
                            'admin_notes' => $request->admin_notes,
                            'bulk_action' => true
                        ]
                    ]);

                    $approved++;

                } catch (\Exception $e) {
                    $errors[] = "Payment {$payment->id}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approved} payments.",
                'approved_count' => $approved,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject payments
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id',
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $payments = Payment::whereIn('id', $request->payment_ids)
                ->where('status', 'pending')
                ->get();

            $rejected = 0;
            $errors = [];

            foreach ($payments as $payment) {
                try {
                    // Reject payment
                    $payment->status = 'rejected';
                    $payment->verified_by = Auth::id();
                    $payment->verified_at = now();
                    $payment->rejection_reason = $request->rejection_reason;
                    $payment->save();

                    // Log the action
                    PaymentLog::create([
                        'payment_id' => $payment->id,
                        'user_id' => $payment->user_id,
                        'admin_id' => Auth::id(),
                        'action' => 'bulk_rejected',
                        'status_before' => 'pending',
                        'status_after' => 'rejected',
                        'data' => [
                            'rejection_reason' => $request->rejection_reason,
                            'bulk_action' => true
                        ]
                    ]);

                    $rejected++;

                } catch (\Exception $e) {
                    $errors[] = "Payment {$payment->id}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$rejected} payments.",
                'rejected_count' => $rejected,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk rejection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}