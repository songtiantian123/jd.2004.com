<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\SeatModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\TicketModel;
use Illuminate\Http\Request;
class ApiController extends Controller{
    // 小程序
    public function test(){
        $goods_info = [
            'goods_id'=>12,
            'goods_name'=>'电视',
            'goods_price'=>3500,
        ];
        echo json_encode($goods_info,true);
    }
}




























