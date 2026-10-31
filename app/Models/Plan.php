<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['features' => 'array', 'custom_domain' => 'boolean', 'is_active' => 'boolean',
            'price_monthly' => 'decimal:2', 'price_yearly' => 'decimal:2', 'commission_rate' => 'decimal:2'];
    }

    public function priceFor(string $cycle): float
    {
        return (float) ($cycle === 'yearly' ? $this->price_yearly : $this->price_monthly);
    }

    public function isFree(): bool
    {
        return (float) $this->price_monthly === 0.0;
    }
}
