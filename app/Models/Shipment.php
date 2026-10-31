<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use BelongsToStore, HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['delivered_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'shipping_company_id');
    }
}
