<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class PaymentSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the payment settings page.
     *
     * @return \Illuminate\View\View
     */
    public function showPaymentSettingsForm()
    {
        // Get current settings from cache with config fallbacks
        $cachedSettings = $this->getCachedSettings();
        
        $settings = [
            'ocr' => [
                'enabled' => $cachedSettings['ocr_enabled'] ?? config('payment.ocr.enabled', true),
                'approval_threshold' => $cachedSettings['ocr_approval_threshold'] ?? config('payment.ocr.approval_threshold', 7),
                'admin_review_threshold' => $cachedSettings['ocr_admin_review_threshold'] ?? config('payment.ocr.admin_review_threshold', 4),
                'save_processed_images' => config('payment.ocr.save_processed_images', true),
                'enhance_image' => config('payment.ocr.enhance_image', true),
            ],
            'verification' => [
                'auto_approval_threshold' => $cachedSettings['auto_approval_threshold'] ?? config('payment.verification.auto_approval_threshold', 70),
                'expedited_review_threshold' => $cachedSettings['expedited_review_threshold'] ?? config('payment.verification.expedited_review_threshold', 40),
                'method_weights' => config('payment.verification.method_weights', [
                    'transaction_pattern' => 5,
                    'ocr' => 3,
                    'metadata' => 2
                ]),
            ],
            'storage' => [
                'enabled' => config('payment.storage.enabled', true),
                'compress_on_upload' => config('payment.storage.compress_on_upload', true),
                'compression_quality' => config('payment.storage.compression_quality', 70),
            ],
            'general' => [
                'reference_expiry_hours' => config('payment.reference_expiry_hours', 24),
                'enable_auto_verification' => $cachedSettings['auto_verification_enabled'] ?? config('payment.enable_auto_verification', true),
            ]
        ];

        // Get OCR status
        $ocrAvailable = $this->checkOcrAvailability();

        return view('admin.payments.settings', compact('settings', 'ocrAvailable'));
    }

    /**
     * Update payment settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePaymentSettings(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'ocr_enabled' => 'boolean',
            'auto_verification_enabled' => 'boolean',
            'ocr_approval_threshold' => 'integer|min:1|max:10',
            'ocr_admin_review_threshold' => 'integer|min:1|max:10',
            'auto_approval_threshold' => 'integer|min:1|max:100',
            'expedited_review_threshold' => 'integer|min:1|max:100',
        ]);

        try {
            // Store settings in cache instead of modifying .env file
            $settingsData = [
                'ocr_enabled' => $request->ocr_enabled ?? false,
                'auto_verification_enabled' => $request->auto_verification_enabled ?? false,
                'ocr_approval_threshold' => $request->ocr_approval_threshold ?? 7,
                'ocr_admin_review_threshold' => $request->ocr_admin_review_threshold ?? 4,
                'auto_approval_threshold' => $request->auto_approval_threshold ?? 70,
                'expedited_review_threshold' => $request->expedited_review_threshold ?? 40,
                'updated_by' => auth()->id(),
                'updated_at' => now()->toIso8601String()
            ];

            // Store in cache with long expiration (24 hours)
            Cache::put('payment_system_settings', $settingsData, 86400);
            
            // Clear related caches
            Cache::forget('payment_system_status');

            // Log the changes
            Log::info('Payment settings updated by admin', [
                'admin_id' => auth()->id(),
                'changes' => $validated,
                'storage_method' => 'cache'
            ]);

            return redirect()->route('payment-settings.form')
                ->with('success', 'Payment settings updated successfully. Changes are stored securely in cache.');
        } catch (\Exception $e) {
            Log::error('Error updating payment settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('payment-settings.form')
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Check if OCR is available on the system.
     *
     * @return bool
     */
    private function checkOcrAvailability()
    {
        try {
            // Safer alternative to exec() - check if tesseract binary exists
            $possiblePaths = [
                '/usr/bin/tesseract',
                '/usr/local/bin/tesseract',
                '/opt/homebrew/bin/tesseract'
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    return true;
                }
            }
            
            // Alternative: check if tesseract command exists using which command safely
            $whichResult = shell_exec('command -v tesseract 2>/dev/null');
            return !empty($whichResult);
        } catch (\Exception $e) {
            \Log::warning('Error checking OCR availability', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get cached payment settings with fallback to config defaults
     *
     * @return array
     */
    private function getCachedSettings(): array
    {
        return Cache::get('payment_system_settings', [
            'ocr_enabled' => config('payment.ocr.enabled', true),
            'auto_verification_enabled' => config('payment.enable_auto_verification', true),
            'ocr_approval_threshold' => config('payment.ocr.approval_threshold', 7),
            'ocr_admin_review_threshold' => config('payment.ocr.admin_review_threshold', 4),
            'auto_approval_threshold' => config('payment.verification.auto_approval_threshold', 70),
            'expedited_review_threshold' => config('payment.verification.expedited_review_threshold', 40),
        ]);
    }
} 