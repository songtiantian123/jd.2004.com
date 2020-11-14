<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrdersModel extends Model{
    // 表名
    protected $table = 'orders';
    protected $primaryKey = 'orders_id';
    public $timestamps = false;
}
