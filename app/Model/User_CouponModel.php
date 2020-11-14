<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User_CouponModel extends Model{
    // 表名
    protected $table = 'p_user_coupon';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
