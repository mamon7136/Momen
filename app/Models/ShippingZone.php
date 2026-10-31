<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingZone extends Model
{
    use BelongsToStore, HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'free_over' => 'decimal:2', 'price_per_kg' => 'decimal:2',
            'cod_allowed' => 'boolean', 'is_active' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'shipping_company_id');
    }

    public function label(): string
    {
        return collect([$this->state, $this->city, $this->area])->filter()->implode(' - ');
    }
}
