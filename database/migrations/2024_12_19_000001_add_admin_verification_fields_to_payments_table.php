<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Transaction information
            $table->string('transaction_id')->nullable()->after('payment_method');
            $table->string('receipt_path')->nullable()->after('transaction_id');
            
            // Admin verification fields
            $table->unsignedBigInteger('verified_by')->nullable()->after('paid_at');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('verified_at');
            $table->text('admin_notes')->nullable()->after('rejection_reason');
            
            // Add indexes for performance
            $table->index('transaction_id');
            $table->index('status');
            $table->index('verified_by');
            $table->index('created_at');
            
            // Foreign key constraint for verified_by
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['verified_by']);
            
            // Drop indexes
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['verified_by']);
            $table->dropIndex(['created_at']);
            
            // Drop columns
            $table->dropColumn([
                'transaction_id',
                'receipt_path',
                'verified_by',
                'verified_at',
                'rejection_reason',
                'admin_notes'
            ]);
        });
    }
};