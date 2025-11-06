<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class VtuProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'api_endpoint',
        'api_key',
        'secret_key',
        'service_type',
        'is_active',
        'commission_rate',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'commission_rate' => 'decimal:4',
            'settings' => 'array',
        ];
    }

    /**
     * Get the transactions for this provider.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope to get active providers only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get providers by service type
     */
    public function scopeByServiceType($query, $serviceType)
    {
        return $query->where('service_type', $serviceType)
                    ->orWhere('service_type', 'both');
    }

    /**
     * Get provider by network code
     */
    public static function getByNetworkCode($code)
    {
        return static::where('code', $code)->active()->first();
    }

    /**
     * Check if provider supports a service type
     */
    public function supportsServiceType($serviceType): bool
    {
        return $this->service_type === 'both' || $this->service_type === $serviceType;
    }

    /**
     * Calculate commission for an amount
     */
    public function calculateCommission(float $amount): float
    {
        return $amount * $this->commission_rate;
    }

    /**
     * Get the encrypted API key
     */
    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Get the encrypted secret key
     */
    protected function secretKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }
}