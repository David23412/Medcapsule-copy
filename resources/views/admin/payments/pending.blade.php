@extends('layouts.app')

@section('title', 'Pending Payments - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-clock text-warning me-2"></i>
                    Pending Payments
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.payments.all') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>All Payments
                    </a>
                    <a href="{{ route('admin.settings.payments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-1"></i>Settings
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Payments
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Completed Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Revenue Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_amount_today'] ?? 0) }} EGP</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Avg Verification Time
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['avg_verification_time'] ? round($stats['avg_verification_time']) . ' min' : 'N/A' }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-stopwatch fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filter Payments
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.payments.pending') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search User</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name or email...">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="method" class="form-label">Payment Method</label>
                            <select class="form-select" id="method" name="method">
                                <option value="">All Methods</option>
                                <option value="vodafone_cash" {{ request('method') == 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash</option>
                                <option value="fawry" {{ request('method') == 'fawry' ? 'selected' : '' }}>Fawry</option>
                                <option value="bank_transfer" {{ request('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.payments.pending') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Pending Payments ({{ $payments->total() }})
                    </h6>
                    
                    @if($payments->count() > 0)
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-tasks me-1"></i>Bulk Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                            <li><a class="dropdown-item text-success" href="#" onclick="bulkApprove()">
                                <i class="fas fa-check me-2"></i>Approve Selected
                            </a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="bulkReject()">
                                <i class="fas fa-times me-2"></i>Reject Selected
                            </a></li>
                        </ul>
                    </div>
                    @endif
                </div>
                
                <div class="card-body p-0">
                    @if($payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="paymentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40px">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Transaction ID</th>
                                    <th>Receipt</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr data-payment-id="{{ $payment->id }}">
                                    <td>
                                        <input type="checkbox" class="payment-checkbox form-check-input" 
                                               value="{{ $payment->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle me-2">
                                                <img src="{{ $payment->user->profile_picture_url ?? '/images/default-avatar.png' }}" 
                                                     alt="Avatar" class="rounded-circle" width="32" height="32">
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $payment->user->name }}</div>
                                                <div class="text-muted small">{{ $payment->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ $payment->course->name ?? 'N/A' }}</div>
                                        @if($payment->course)
                                        <div class="text-muted small">{{ $payment->course->description ?? '' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info font-size-12">{{ number_format($payment->amount) }} EGP</span>
                                    </td>
                                    <td>
                                        @php
                                            $methodInfo = [
                                                'vodafone_cash' => ['icon' => 'fas fa-mobile-alt', 'class' => 'danger', 'name' => 'Vodafone Cash'],
                                                'fawry' => ['icon' => 'fas fa-store', 'class' => 'warning', 'name' => 'Fawry'],
                                                'bank_transfer' => ['icon' => 'fas fa-university', 'class' => 'primary', 'name' => 'Bank Transfer']
                                            ];
                                            $method = $methodInfo[$payment->payment_method] ?? ['icon' => 'fas fa-credit-card', 'class' => 'secondary', 'name' => 'Other'];
                                        @endphp
                                        
                                        <span class="badge bg-{{ $method['class'] }}">
                                            <i class="{{ $method['icon'] }} me-1"></i>{{ $method['name'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="text-primary">{{ $payment->transaction_id ?? 'N/A' }}</code>
                                    </td>
                                    <td class="text-center">
                                        @if($payment->receipt_path)
                                        <button class="btn btn-sm btn-outline-info" onclick="viewReceipt({{ $payment->id }})">
                                            <i class="fas fa-image"></i>
                                        </button>
                                        @else
                                        <span class="text-muted">No receipt</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            {{ $payment->created_at->format('M d, Y') }}
                                            <br>
                                            {{ $payment->created_at->format('H:i') }}
                                            <span class="badge bg-light text-dark">{{ $payment->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-success" 
                                                    onclick="approvePayment({{ $payment->id }})"
                                                    title="Approve Payment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="rejectPayment({{ $payment->id }})"
                                                    title="Reject Payment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-info" 
                                                    onclick="viewPayment({{ $payment->id }})"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning" 
                                                    onclick="requestInfo({{ $payment->id }})"
                                                    title="Request More Info">
                                                <i class="fas fa-question"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-inbox fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">No pending payments found</h5>
                        <p class="text-muted">All payments have been processed or no payments match your filters.</p>
                    </div>
                    @endif
                </div>
                
                @if($payments->hasPages())
                <div class="card-footer">
                    {{ $payments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approvalModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approve Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this payment? The user will be enrolled in the course immediately.</p>
                <div class="mb-3">
                    <label for="adminNotes" class="form-label">Admin Notes (Optional)</label>
                    <textarea class="form-control" id="adminNotes" rows="3" 
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmApproval()">
                    <i class="fas fa-check me-1"></i>Approve Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectionModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Reject Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please provide a reason for rejecting this payment:</p>
                <div class="mb-3">
                    <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <select class="form-select" id="rejectionReason" required>
                        <option value="">Select a reason...</option>
                        <option value="Invalid transaction ID">Invalid transaction ID</option>
                        <option value="Insufficient amount">Insufficient amount</option>
                        <option value="Duplicate payment">Duplicate payment</option>
                        <option value="Poor receipt quality">Poor receipt quality</option>
                        <option value="Suspicious activity">Suspicious activity</option>
                        <option value="Other">Other (specify below)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="rejectionNotes" class="form-label">Additional Notes</label>
                    <textarea class="form-control" id="rejectionNotes" rows="3" 
                              placeholder="Provide additional details..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejection()">
                    <i class="fas fa-times me-1"></i>Reject Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">
                    <i class="fas fa-image me-2"></i>Payment Receipt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="receiptImage" src="" alt="Receipt" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Global variables
let currentPaymentId = null;

// CSRF token setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Functions for payment actions
function approvePayment(paymentId) {
    currentPaymentId = paymentId;
    document.getElementById('adminNotes').value = '';
    new bootstrap.Modal(document.getElementById('approvalModal')).show();
}

function confirmApproval() {
    const adminNotes = document.getElementById('adminNotes').value;
    
    $.ajax({
        url: `/admin/payments/${currentPaymentId}/approve`,
        method: 'POST',
        data: {
            admin_notes: adminNotes
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                location.reload();
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to approve payment';
            showAlert('danger', message);
        }
    });
    
    bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();
}

function rejectPayment(paymentId) {
    currentPaymentId = paymentId;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectionNotes').value = '';
    new bootstrap.Modal(document.getElementById('rejectionModal')).show();
}

function confirmRejection() {
    const rejectionReason = document.getElementById('rejectionReason').value;
    const rejectionNotes = document.getElementById('rejectionNotes').value;
    
    if (!rejectionReason) {
        showAlert('warning', 'Please select a rejection reason');
        return;
    }
    
    const fullReason = rejectionNotes ? `${rejectionReason}: ${rejectionNotes}` : rejectionReason;
    
    $.ajax({
        url: `/admin/payments/${currentPaymentId}/reject`,
        method: 'POST',
        data: {
            rejection_reason: fullReason,
            admin_notes: rejectionNotes
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                location.reload();
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to reject payment';
            showAlert('danger', message);
        }
    });
    
    bootstrap.Modal.getInstance(document.getElementById('rejectionModal')).hide();
}

function viewReceipt(paymentId) {
    $.ajax({
        url: `/api/admin/payments/${paymentId}/receipt`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                document.getElementById('receiptImage').src = response.receipt_url;
                new bootstrap.Modal(document.getElementById('receiptModal')).show();
            } else {
                showAlert('warning', 'Receipt not available');
            }
        },
        error: function() {
            showAlert('danger', 'Failed to load receipt');
        }
    });
}

function viewPayment(paymentId) {
    window.open(`/admin/payments/${paymentId}`, '_blank');
}

function requestInfo(paymentId) {
    const infoRequest = prompt('What additional information do you need from the user?');
    if (infoRequest) {
        $.ajax({
            url: `/admin/payments/${paymentId}/request-info`,
            method: 'POST',
            data: {
                info_request: infoRequest
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Failed to send information request');
            }
        });
    }
}

function bulkApprove() {
    const selectedPayments = getSelectedPayments();
    if (selectedPayments.length === 0) {
        showAlert('warning', 'Please select payments to approve');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selectedPayments.length} payments?`)) {
        $.ajax({
            url: '/admin/payments/bulk/approve',
            method: 'POST',
            data: {
                payment_ids: selectedPayments
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Bulk approval failed');
            }
        });
    }
}

function bulkReject() {
    const selectedPayments = getSelectedPayments();
    if (selectedPayments.length === 0) {
        showAlert('warning', 'Please select payments to reject');
        return;
    }
    
    const reason = prompt('Enter rejection reason for all selected payments:');
    if (reason) {
        $.ajax({
            url: '/admin/payments/bulk/reject',
            method: 'POST',
            data: {
                payment_ids: selectedPayments,
                rejection_reason: reason
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Bulk rejection failed');
            }
        });
    }
}

function getSelectedPayments() {
    const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => checkbox.value);
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
}

// Auto-refresh every 30 seconds
setInterval(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.toString()) {
        location.reload();
    }
}, 30000);
</script>
@endsection