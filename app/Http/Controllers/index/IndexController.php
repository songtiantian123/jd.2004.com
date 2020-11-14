<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\SignModel;
use App\Model\UserModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\OrdersModel;// 订单表
use App\Model\GoodsModel; // 商品表
use App\Model\Orders_goodsModel; // 订单商品表
use Illuminate\Http\Request;
class IndexController extends Controller{
    /** 前台首页*/
    public function index(Request $request){
        // 猜你喜欢
        $uid =  session('uid');// 从session取出用户id
        $info =[];
        // 查询用户最近购买的
        $order_id = OrdersModel::where([['user_id','=',$uid],['pay_status','=',1]])->select('order_id')->get();
        if(empty($order_id)){
            foreach ($order_id as $k=>$v){
                $goodsInfo = Orders_goodsModel::where('order_id',$v['order_id'])->select('goods_id')->get();
                foreach($goodsInfo as $key=>$val){
                    $info[]=GoodsModel::find($val['goods_id']);
                }
            }
        }
        // 查询用户最近订单
        $order_id = OrdersModel::where('user_id',$uid)->select('order_id')->get();
        if(!empty($order_id)){
            foreach ($order_id as $keys=>$value){
                $goodsInfo = Orders_goodsModel::where('order_id',$value['order_id'])->select('goods_id')->get();
                foreach ($goodsInfo as $ke=>$vl){
                    $info[] =GoodsModel::find($vl['goods_id']);
                }
            }
        }
        $info = array_unique($info,SORT_REGULAR);
        $info_count = count($info);
        // 随机从数据库中取出条数 最为最后的补充
        if($info_count<12){
            $info_count =12 -$info_count;
            $random = GoodsModel::orderByRaw("RAND()")->limit($info_count)->get();
            foreach ($random as $k=>$v){
                $info[]=$v;
            }
        }
        $info = array_unique($info,SORT_REGULAR);
        $random=[];
        foreach ($info as $k=>$v){
            $random[]=$v;
        }
        $random = array_chunk($info,2);
        $Goods = GoodsModel::limit(4)->get();// 查询临时推荐商品
        return view('index.index',['random'=>$random,'Goods'=>$Goods]);//
    }
    /** 前台登录页*/
    public function login(){
        return view('index.login');
    }
    /** 我的品优购*/
    public function center(){
        return view('/index/center');
    }
    /** 我的收藏*/
    public function collect(){
        return view('/index/collect');
    }
    /** 签到页面*/
    public function sign(){
        $uid = session('uid');
        // 根据用户id查询是否签到
        $sign = SignModel::where('user_id',$uid)->first();
        if(empty($sign)){
            $sign = 1;
        }else{
            $sign=0;
        }
        return view('/index/sign',['sign'=>$sign]);
    }
    /** 签到*/
    public function signDo(){
        $uid = session('uid');// 从session取出用户id
        if(empty($uid)){
            $data =[
                'error' =>400008,
                'msg' =>'请先登录',
            ];
            return json_encode($data,true);
        }
        // 检测用户是否签到
        $time1 = strtotime(date("Y-m-d"));
        $res = SignModel::where(['user_id'=>$uid])->where('add_time','>=',$time1)->first();
        if($res){
            $data=[
                'error'=>30008,
                'msg'=>'你今天已签到,明天再来'
            ];
            return json_encode($data,true);
        }
        // 添加数据
        $data=[
            'user_id'=>$uid,
            'is_sign'=>1,
            'add_time'=>time(),
        ];
        // 入库
        $res = SignModel::insert($data);// 往签到表添加数据
        if($res){
            $sign = SignModel::where('user_id',$uid)->first();
            if(is_object($sign)){
                $sign = $sign->toArray();
            }
            $data = [
                'integral'=>$sign['integral']+100,
            ];
            SignModel::where('user_id',$uid)->update($data);
            //dd($userInfo);
            $data=[
                'error'=>200,
                'msg'=>'签到成功,积分+100',
            ];
            return json_encode($data,true);
        }else{
            $data=[
                'error'=>5000001,
                'msg'=>'签到失败',
            ];
            return $data;
        }
    }
}
