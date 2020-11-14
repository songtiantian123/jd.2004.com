<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\PrizeModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\OrdersModel;// 订单表
use App\Model\GoodsModel; // 商品表
use App\Model\Orders_goodsModel; // 订单商品表
use Illuminate\Http\Request;
class PrizeController extends Controller{
    /** 抽奖页面*/
    public function index(Request $request)
    {
       return view('prize.index');
    }
    /** 开始抽奖*/
    public function add(){
        //echo date("Y-m-d H:i:s",1603728000);die;
        $uid = session('uid');// 从session中取出id
        //$uid = 12;// 测试
        if(empty($uid)){// 判断用户是否登录
            $res = [
                'error' => 2004,
                'msg' => '请先登录',
            ];
            return json_encode($res,true);
        }
        // 检查用户当天是否已有抽奖记录
        $time1 = strtotime(date("Y-m-d"));
        //echo $time1;die;
        $res = PrizeModel::where(['user_id'=>$uid])->where('add_time','>=',$time1)->first();
        //var_dump($res);
        if($res){
            $res = [
                'error' => 300000,
                'msg' => '今天已抽奖,明天再来',
            ];
            return json_encode($res,true);
        }
        $rand = mt_rand(1,100000);
        $level = 0;
        if($rand>=1 && $rand<=10){// 一等奖
            $level = 1;
        }elseif($rand>=11 && $rand<=30){// 二等奖
            $level = 2;
        }elseif($rand>=31 && $rand<=60){// 三等奖
            $level = 3;
        }

        // 记录抽奖数据
        $d = [
            'user_id' =>$uid,
            'add_time' => time(),
            'level' => $level,// 中将等级
            'is_prize' => 0,// 是否中奖 0 没有 1 有
        ];

        $pid = PrizeModel::insertGetId($d);
        // 是否记录成功
        if($pid>0){
            $data = [
                'error' => 200,
                'msg' => 'ok',
                'data' => [
                    'rand' =>$rand,
                    'level'=>$level,
                ],
            ];
        }else{
            // 异常
            $data=[
                'error'=>500000,
                'msg' =>"数据异常,请重试",
            ];
        }
        return $data;
    }
}
