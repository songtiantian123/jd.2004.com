<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\OrdersModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\CategoryModel;
use Illuminate\Http\Request;
use Illuminate\Http\DB;
use SebastianBergmann\CodeCoverage\TestFixture\C;

class CartController extends Controller{
    /** 购物车列表页面*/
    public function index(Request $request){
        $uid = session('uid');// 用户id
        if(empty($uid)){// 判断用户id
            return redirect('user/login')->with(['msg'=>'请先登录']);
        }
        // TODO 用户浏览购物车列表时如果某件商品库存不足10个,则显示提醒信息:库存紧张
        $where=[
            'uid'=>$uid,
        ];
        $goods_num = CartModel::where($where)->first();
        if($goods_num['goods_num']>10){
               echo '库存充足';
        }else if($goods_num['goods_num']<10){
               echo '库存紧张';
        }
        $CartInfo = CartModel::where('uid',$uid)->get();
        $arr = [];
        foreach ($CartInfo as $k=>$v){
            if(is_object($v)){
                $v = $v->toArray();
            }
            $cat_id = GoodsModel::where('goods_id',$v['goods_id'])->first();
            $v['goods_name'] = $cat_id['goods_name'];
            $v['goods_img'] = $cat_id['goods_img'];
            $v['shop_price'] = $cat_id['shop_price'];
            $cat_id = $cat_id['cat_id'];
//            dd($cat_id);
            $cate = CategoryModel::find($cat_id);
            $cate_name = $cate['cat_name'];
            $arr[$k]['cat_name'] = $cate_name;
            $arr[$k]['child'][$k] = $v;
        }
        $CartInfo = $arr;
        // TODO 商品是否下架
        $cart = CartModel::first();
        $is_delete = $cart['is_delete'];
        if($is_delete){
            $is_delete=1;
        }else{
            $is_delete=2;
        }
        return view('/cart/index',['CartInfo'=>$CartInfo,'is_delete'=>$is_delete]);
    }
    /** 购物车添加*/
    public function add(Request $request){
        $goods_id = $request->goods_id;// 获取商品id
//        echo $goods_id;die;
        $goods_num = $request->goods_num;// 获取goods_num数量
        if(empty($goods_id)){
            return view('/user/login')->with(['msg'=>'非法操作']);
            //echo json_encode(['error'=>400001,'msg'=>'非法操作']);
        }
        if(empty($shop_count)){
            $shop_count =1;
        }
          // echo 'id：'.$goods_id;
        //判断用户是否登录
        $uid = session('uid');
        if(empty($uid)){
            return view('/user/login')->with(['msg'=>'请先登录']);
           //  echo json_encode(['error'=>400001,'msg'=>'请先登录']);
        }
        $data = [
            'goods_id' =>$goods_id,
            'add_time' =>time(),
            'goods_num' =>$goods_num,
            'uid' =>$uid,
        ];
        $where = [
            'goods_id' =>$goods_num,
            'uid' =>$uid,
        ];
        $one = CartModel::where($where)->first();// 根据用户id查询购物车
        if(empty($one)){
            $res = CartModel::insert($data);
        }else{
            $data['goods_num'] = $one['goods_num']+$goods_num;
            $res = CartModel::where($where)->update($data);
        }
        if($res){
            return redirect('/cart/index');
            echo json_encode(['error'=>1,'msg'=>'添加购物车成功']);
        }else{
            echo json_encode(['error'=>40001,'msg'=>'添加购物车失败']);
        }
    }
    /** 添加购物车后*/
    public function success(Request $request){
        $uid = session('uid');
        if(empty($uid)){
            return redirect('user/login')->with(['msg'=>'请先登录']);
        }
        $goods_id = $request->goods_id;
        if(empty($goods_id)){
            return redirect('/');
        }
        $Cart = GoodsModel::form('p_goods as g')
            ->leftjoin('cart as c','g.goods_id','=','c.goods_id')
            -where('g.goods_id',$goods_id)
            -first();
//        print_r($Cart->toArray());die;
        if(empty($Cart)){
            return redirect('/');
        }
        return view('cart.go',['Cart'=>$Cart]);
    }
    /** 生成订单*/
    public function getOrderInfo(Request $request){
        // 查询订单表
        $orders = CartModel::get();
        // TODO 用户浏览购物车列表时如果某件商品库存不足10个,则显示提醒信息:库存紧张
        $goods_id = $request->get('goods_id');
        $goods_number=10;
        $goods_num = GoodsModel::first();
        if(is_object($goods_num)){
            $goods_num = $goods_num->toArray();
        }
        if($goods_num['goods_number']<=$goods_number){
            $res = [
                'error'=>400004,
                'msg'=>'库存紧张',
            ];

        }
        return view('cart/getOrderInfo',['orders'=>$orders]);
    }
    /* 加入购物车
    public function add(Request $request){
        $uid = session()->get('uid');
        if(empty($uid)){
            $data=[
                'error' => 400001,
                'msg' => '请先登录',
            ];

        }
        $goods_id = $request->get('id');
        $goods_num = $request->get('num',1);
        // 检查是否下架 库存是否充足
        // 购物车保存信息
        $cart_info = [
            'goods_id' =>$goods_id,
            'uid' => $uid,
            'goods_num' =>$goods_num,
            'add_time' =>time(),
        ];
        $res = CartModel::insertGetId($cart_info);
        if($res>0){
            $data = [
                'error' =>0,
                'msg' => '成功加入购物车',
            ];
//            echo json_encode($data);
            return view('cart/index');
        }else{
            $data = [
                'error' =>500001,
                'msg' => '加入购物车失败',
            ];
//            echo json_encode($data);
        }

    }
*/
    /** 删除*/
    public function delete(Request $request){
        echo '删除';
    }
}
