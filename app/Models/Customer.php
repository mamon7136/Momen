<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToStore, HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['phone_verified_at' => 'datetime', 'total_spent' => 'decimal:2'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
