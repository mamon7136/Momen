<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToStore, HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['gateway_payload' => 'array', 'reviewed_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
