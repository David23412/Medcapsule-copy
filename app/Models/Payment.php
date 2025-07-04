<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'reference_number',
        'amount',
        'payment_method',
        'transaction_id',
        'receipt_path',
        'status',
        'payment_data',
        'paid_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'admin_notes',
    ];

    protected $casts = [
        'payment_data' => 'json',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that was paid for
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the admin who verified this payment
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Mark a payment as completed and enroll the user in the course
     */
    public function markAsPaid()
    {
        $this->status = 'completed';
        $this->paid_at = now();
        $this->save();

        // Enroll the user in the course
        $this->user->courses()->syncWithoutDetaching([$this->course_id]);

        // Create a notification
        app(NotificationService::class)->createPaymentSuccessNotification(
            $this->user,
            $this->course->name,
            $this->amount
        );

        return $this;
    }
}
