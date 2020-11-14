<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GithubUserModel extends Model{
    // 表名
    protected $table = 'p_github';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
