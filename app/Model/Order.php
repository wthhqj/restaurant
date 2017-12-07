<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';
    protected $dates = ['deleted_at'];
    protected $hidden = ['deleted_at', 'updated_at'];

    protected $casts = [
        'cart_list' => 'array',
    ];
}
