<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCompany extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['config' => 'array', 'is_active' => 'boolean'];
    }

    public function trackingLink(?string $tracking): ?string
    {
        return $this->tracking_url && $tracking ? str_replace('{tracking}', $tracking, $this->tracking_url) : null;
    }
}
