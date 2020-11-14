<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PrizeModel extends Model{
    // 表名
    protected $table = 'p_prize';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
