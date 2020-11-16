<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Wx_UserModel extends Model
{
    // 表名
    protected $table = 'wx_user';
    //主键自增
    protected $primaryKey = 'id';
    public $timestamps = false;
}
