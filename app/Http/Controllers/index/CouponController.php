<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
use App\Model\User_CouponModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Model\Goods_CouponModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\ShopModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CouponController extends Controller{
    /** 优惠券*/
    public function coupon(){
        // TODO 查询出所有的优惠卷
        $CouponInfo = Goods_CouponModel::where('is_start',1)->get();
        if(is_object($CouponInfo)){
            $CouponInfo = $CouponInfo->toArray();
        }
        return view('/index/coupon',['CouponInfo'=>$CouponInfo]);
    }
    /** 领取优惠券*/
    public function receive(Request $request){
        //echo __METHOD__;die;
        //todo 判断用户是否登录
        $uid = session('uid');
        $coupon_id = $request->coupon_id;
        $coupon = Goods_CouponModel::where('coupon_id',$coupon_id)->first();
        if(empty($coupon)){
            return redirect('/')->with(['非法操作']);
        }
        if(is_object($coupon)){
            $coupon = $coupon->toArray();
        }
        $begin_time = strtotime("2020-11-11");// 生效时间
        $expire_time = strtotime("2020-11-12");// 过期时间
        $data = [
            'user_id'=>$uid,//用户id
            'coupon_num'=>Str::random(30),
            'add_time'=>time(),// 领卷时间
            'begin_time'=>$begin_time,//生效时间
            'expire_time'=>$expire_time,// 过期时间
            'coupon_time'=>strtotime(date('Y-m-d H:i:s',strtotime('+1day'))),
            'coupon_id'=>$coupon_id,// 卷的类型
        ];
        $res = User_CouponModel::insert($data);
        if($res){
            return redirect('/cart/index');
        }else{
            return redirect('/coupon');
        }
    }
}
