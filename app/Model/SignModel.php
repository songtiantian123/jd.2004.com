<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
//use Encore\Admin\Traits\ModelTree;
class SignModel extends Model{
    // 表名
//    use ModelTree;
    protected $table = 'p_sign';
    protected $primaryKey = 'id';
    public $timestamps = false;
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->setParentColumn('parent_id');
//        $this->setOrderColumn('sort_order');
//        $this->setTitleColumn('cat_name');
//    }
}
