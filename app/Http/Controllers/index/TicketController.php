<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\SeatModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\TicketModel;
use Illuminate\Http\Request;
class TicketController extends Controller{
    /** 购票*/
    public function film(Request $request){
        $film_id = $request->film_id;// 接收id
        $filmInfo = TicketModel::where('film_id',$film_id)->first();//根据film_id查询一条数据
        if(is_object($filmInfo)){
            $filmInfo = $filmInfo->toArray();
        }
        // TODO 剩余库存
        $film_count = $filmInfo['film_count'];
        $str=[];
        for($i=1;$i<=$film_count;++$i){
            $str[] =[
                'seat_num'=>$i,
            ];
        }
        // TODO 根据电影id查询电影已经购买当前座位号
        $seatInfo = SeatModel::where('film_id',$film_id)->get();
        if(is_object($seatInfo)){
            $seatInfo = $seatInfo->toArray();
        }
        $seat_num = [];
        foreach($seatInfo as $k=>$v){
            $seat_num[]=$v['seat_num'];
        }
        return view('/film/index',['film_count'=>$str,'seat_num'=>$seat_num]);
    }
    /** 开始购票*/
    public function filmadd(Request $request){
        // TODO 从session中取出id
        $uid = session('uid');
        if(empty($uid)){
            echo "<script>alert('请先登录');window.location.href ='/user/login'</script>";die;
            }
        $data = $request->except('_token');
        $film_id = $data['film_id'];
        if(empty($data['file_count'])){
            echo "<script>alert('请选择电影座');window.location.href='/film?film_id='+$film_id</script>";
        }
        $film_count = $data['film_count'];
        // TODO 根据id查询当前电影已经购买当前座位号
        $seatInfo = SeatModel::where('film_id',$film_id)->get();
        if(is_object($seatInfo)){
            $seatInfo = $seatInfo->toArray();
        }
        $seat_num =[];
        foreach ($seatInfo as $k=>$v){
            $seat_num[] = $v['seat_num'];
        }
        // TODO 入库
        $data =[];
        foreach ($film_count as $k=>$v){
            if(in_array($v,$seat_num)){
                echo "<script>alert($v+'座位号已被购买,重新选择');window.location.href='/film?film_id='+$film_id</script>";
            }else{
                $data[]=[
                    'film_id' =>$film_id,
                    'seat_num' =>$v,
                    'add_time' => time(),
                    'user_id' =>$uid,
                ];
            }
        }
        // todo 入库
         $res = SeatModel::insert($data);
            if($res){
                echo "<script>alert('购票成功,前台查询后,付款拿票');window.location.href='/film?film_id='+$film_id</script>";
            }else{
                echo "<script>alert('购票失败');window.location.href='/film?film_id='+$film_id</script>";
            }
        }
    }





























