<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Orders_goodsModel extends Model{
    // 表名
    protected $table = 'orders_goods';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
}
