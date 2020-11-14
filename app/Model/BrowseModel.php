<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BrowseModel extends Model{
    // 表名
    protected $table = 'p_browse';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
