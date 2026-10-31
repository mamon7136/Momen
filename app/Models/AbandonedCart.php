<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    use BelongsToStore, HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['items' => 'array', 'reminded_at' => 'datetime'];
    }
}
