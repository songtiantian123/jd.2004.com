<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\User_CouponModel;
use App\Model\UserModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\GoodsModel;
use App\Model\OrdersModel;
use App\Model\CartModel;
use App\Model\Orders_goodsModel;
use Illuminate\Http\Request;
class   OrdersController extends alipayController {
    /** 下订单列表*/
    public function Orders(){
        $orders = OrdersModel::get();// 查询订单
        return view('orders/orders_list',['orders'=>$orders]);
    }
    /** 生成订单*/
    public function orderDetail(){

        return view('/orders/orderDetail');
    }

    /** 入订单表 和订单商品表*/
    public function pay(Request $request){
        // TODO 查询购物车中的商品
        $uid= session('uid');//从session中取出用户id 判断用户是否登录
        if(empty($uid)){
            return redirect('/user/login')->with(['msg'=>'请先登录']);
        }
        $coupon_id = $request->coupon_id;
        if(!empty($coupon_id)){
            $where = [
                ['user_id',$uid],
                ['c.conpon_id','=',$coupon_id]
            ];
        }
        $coupon = User_CouponModel::where($where)->leftjoin('p_goods_coupon as c','c.coupon_id','=','p_user_coupon.coupon_id')
            ->first();
        dd($coupon);
        $user_name = session('user_name');
        $CartInfo = CartModel::where('uid',$uid)->get();
        $money=0;
        foreach($CartInfo as $k=>$v){
            $goodsInfo = GoodsModel::where('goods_id',$v['goods_id'])->first(['shop_price']);
            $money = $money+$goodsInfo['shop_price'];
        }
        // TODO 生成订单号
        $order_id_main = date('YmdHis').rand(10000000,9999999);
        $order_id_len = strlen($order_id_main);
        for($i=0;$i<$order_id_len;$i++){
            $order_id_sum = (int)(substr($order_id_main,$i,1));
        }
        // TODO 支付宝平台订单号
        $osn = $order_id_main.str_pad((100-$order_id_sum)%100,2,'0',STR_PAD_LEFT);
        // TODO 生成订单
        $data =[
            'order_sn' =>$osn,// 订单号
            'user_id' => $uid,
            'order_status' =>0,// 订单状态
            'shipping_status' =>0,// 发货状态
            'pay_status' =>1,// 支付状态
            'consignee' =>$user_name,// 收货人
            'country' => '中国',// 国家
            'province' =>'省',// 省
            'city' =>'城市',//城市
            'district' =>'区域',// 区域
            'best_time' => time(),// 订单时间
            'postscript' =>'无',// 备注
            'shipping_id' =>null,// 配送地址IP
            'shipping_name' =>'家',// 运输名称
            'pay_type' =>1,// 支付类型
            'plat_oid' =>$osn,// 支付平台订单号
        ];
        $order_id = OrdersModel::insertGetId($data);
        $Cart = CartModel::where('uid',$uid)->get();
        if(is_object($Cart)){
            $Cart = $Cart->toArray();
        }
        // 得到订单的id 并且去添加订单的商品信息
        foreach ($Cart as $k=>$v){
            $goods_id = $v['goods_id'];
            $res = GoodsModel::find($goods_id)->toArray();
            $data =[
                'order_id' =>$order_id,
                'goods_id' =>$res['goods_id'],
                'goods_name' =>$res['goods_name'],
                'goods_sn' =>$res['goods_sn'],
                'goods_number' =>$res['goods_number']-1,
                'market_price' =>$res['shop_price'],
                'shop_price' =>$res['shop_price'],
                'goods_attr' =>'',// 商品属性
                'send_number' =>1,// 发件人
                'is_real' =>1,// 是否真实
                'cat_id' =>$res['cat_id'],
                'parent_id' => $res['cat_id'],// 父类id
                'is_gift' =>0,// 是否礼物
                'goods_attr_id' =>'',//商品属性id
            ];
            Orders_goodsModel::insertGetId($data);
        }
        $order =[
            'out_trade_no' =>$osn,
            'total_amount' =>$money,
            'subject' =>'购物车',
        ];
        // TODO 跳转支付页面
        return $this->Alipay($order);
    }
}
