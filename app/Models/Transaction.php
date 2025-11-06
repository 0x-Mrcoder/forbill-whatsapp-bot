<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'vtu_provider_id',
        'recipient_phone',
        'service_type',
        'network_code',
        'amount',
        'commission',
        'provider_amount',
        'status',
        'provider_reference',
        'provider_response',
        'payment_reference',
        'payment_method',
        'failure_reason',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission' => 'decimal:2',
            'provider_amount' => 'decimal:2',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->reference) {
                $transaction->reference = 'TXN_' . strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the VTU provider for the transaction.
     */
    public function vtuProvider()
    {
        return $this->belongsTo(VtuProvider::class);
    }

    /**
     * Get the payment for the transaction.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope to get transactions by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recent transactions
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark transaction as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(string $providerReference = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'provider_reference' => $providerReference,
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Calculate total amount including commission
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount + $this->commission;
    }

    /**
     * Get formatted recipient phone
     */
    public function getFormattedRecipientPhoneAttribute(): string
    {
        $phone = $this->recipient_phone;
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            return '+234' . substr($phone, 1);
        }
        return $phone;
    }
}