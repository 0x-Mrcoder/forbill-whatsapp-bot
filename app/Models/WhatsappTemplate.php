<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'message_text',
        'variables',
        'language',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to get active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get template by name
     */
    public static function getByName(string $name): ?self
    {
        return static::where('name', $name)->active()->first();
    }

    /**
     * Render template with variables
     */
    public function render(array $variables = []): string
    {
        $message = $this->message_text;

        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * Get required variables for this template
     */
    public function getRequiredVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Check if all required variables are provided
     */
    public function hasAllRequiredVariables(array $variables): bool
    {
        $required = $this->getRequiredVariables();
        
        foreach ($required as $variable) {
            if (!array_key_exists($variable, $variables)) {
                return false;
            }
        }

        return true;
    }
}