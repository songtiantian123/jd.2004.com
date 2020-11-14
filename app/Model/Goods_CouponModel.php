<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Goods_CouponModel extends Model{
    // 表名
    protected $table = 'p_goods_coupon';
    protected $primaryKey = 'coupon_id';
    public $timestamps = false;
}
