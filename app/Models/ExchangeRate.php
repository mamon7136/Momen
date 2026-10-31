<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function toSdg(float $amount, string $currency): float
    {
        if ($currency === 'SDG') {
            return $amount;
        }
        $rate = static::where('currency', $currency)->value('rate_to_sdg');

        return round($amount * (float) ($rate ?? 1), 2);
    }
}
