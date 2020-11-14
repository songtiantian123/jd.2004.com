<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CollectModel extends Model{
    // 表名
    protected $table = 'p_collect';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
