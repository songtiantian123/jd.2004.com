<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TicketModel extends Model{
    // 表名
    protected $table = 'p_ticket';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
