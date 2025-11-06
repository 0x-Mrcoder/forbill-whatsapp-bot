<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConversationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'whatsapp_phone',
        'current_step',
        'context_data',
        'current_transaction_id',
        'last_activity_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'context_data' => 'array',
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            $session->last_activity_at = now();
            $session->expires_at = now()->addMinutes(30); // 30 minutes session timeout
        });

        static::updating(function ($session) {
            $session->last_activity_at = now();
            $session->expires_at = now()->addMinutes(30);
        });
    }

    /**
     * Get the user that owns the conversation session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current transaction being processed.
     */
    public function currentTransaction()
    {
        return $this->belongsTo(Transaction::class, 'current_transaction_id');
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if session is idle
     */
    public function isIdle(): bool
    {
        return $this->current_step === 'idle';
    }

    /**
     * Reset session to idle state
     */
    public function resetToIdle(): void
    {
        $this->update([
            'current_step' => 'idle',
            'context_data' => null,
            'current_transaction_id' => null,
        ]);
    }

    /**
     * Update session step
     */
    public function updateStep(string $step, array $contextData = null): void
    {
        $updateData = ['current_step' => $step];
        
        if ($contextData !== null) {
            $updateData['context_data'] = array_merge($this->context_data ?? [], $contextData);
        }

        $this->update($updateData);
    }

    /**
     * Set current transaction
     */
    public function setCurrentTransaction(Transaction $transaction): void
    {
        $this->update(['current_transaction_id' => $transaction->id]);
    }

    /**
     * Get context value
     */
    public function getContext(string $key, $default = null)
    {
        return data_get($this->context_data, $key, $default);
    }

    /**
     * Set context value
     */
    public function setContext(string $key, $value): void
    {
        $contextData = $this->context_data ?? [];
        data_set($contextData, $key, $value);
        $this->update(['context_data' => $contextData]);
    }

    /**
     * Clear context data
     */
    public function clearContext(): void
    {
        $this->update(['context_data' => null]);
    }

    /**
     * Extend session expiry
     */
    public function extend(int $minutes = 30): void
    {
        $this->update(['expires_at' => now()->addMinutes($minutes)]);
    }

    /**
     * Get or create session for a user
     */
    public static function getOrCreateForUser(User $user, string $whatsappPhone): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            ['whatsapp_phone' => $whatsappPhone]
        );
    }
}