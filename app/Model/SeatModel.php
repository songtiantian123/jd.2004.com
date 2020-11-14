<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SeatModel extends Model{
    // 表名
    protected $table = 'p_seat';
    protected $primaryKey = 'seat_id';
    public $timestamps = false;
}
