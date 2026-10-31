<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use BelongsToStore, HasFactory;

    protected $table = 'order_status_history';

    protected $guarded = ['id'];
}
