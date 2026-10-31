<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePaymentMethod extends Model
{
    use BelongsToStore, HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['config' => 'array', 'is_active' => 'boolean', 'fee_percent' => 'decimal:2'];
    }
}
