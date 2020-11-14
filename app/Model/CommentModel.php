<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CommentModel extends Model{
    // 表名
    protected $table = 'p_comment';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
