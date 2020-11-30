<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\Wx_UserModel;
use App\Model\Xcx_UserModel;
use App\Model\Xcx_GoodsDescModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\TicketModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use SebastianBergmann\CodeCoverage\TestFixture\C;

class ApiController extends Controller
{
    // 小程序
    /**
     * 关闭调试
     */
    public function __construct()
    {
//        app('debugbar')->disable();
    }


    /**
     * 首页的商品列表
     */
    public function goodslist(Request $request)
    {
        //$res = GoodsModel::select('goods_id','goods_name','shop_price','goods_img')->limit(10)->get()->toArray();
        $page_size = $request->get('ps');
        $res = GoodsModel::select('goods_id', 'goods_name', 'shop_price', 'goods_img')->paginate($page_size);
        $respons = [
            'error' => 0,
            'msg' => 'ok',
            'data' => [
                'list' => $res->items()
            ],
        ];
        return $respons;
    }

    /**
     * 商品详情(一件商品的详细信息)
     */
    public function getDetails(Request $request)
    {
//        $token = $request->get('access_token');
//        // 验证token是否有效
//        $token_key = 'h:xcx:login:' . $token;
//        echo '<pre>' . 'key: >>>>>' . $token_key;echo '</pre>';
//        $res = Redis::get($token_key);
////        echo $res;die;
//        // 检查token是否存在
//        $status = Redis::exists($token_key);
////        dd($status);
//        if ($status == 0) {
//            $reponse = [
//                'error' => 400004,
//                'msg' => '未授权'
//            ];
//            return $reponse;
//        }
        $goods_id = $request->get('goods_id');
        $getDetaols = GoodsModel::find($goods_id);
        if($getDetaols){
            // 商品描述图片
            $desc_img = Xcx_GoodsDescModel::select('src')->where(['goods_id'=>$goods_id])->get()->toArray();
            $getDetaols->desc_img = array_column($desc_img,'src');
            // 假图片 商品传播相册切换
            $getDetaols->gallery=[
                'https://img.alicdn.com/imgextra/i2/2206434878500/O1CN01FrVvMm2Cf3BNGIjSd_!!2206434878500.jpg_430x430q90.jpg',
                'https://img.alicdn.com/bao/uploaded/i3/2360209412/O1CN01DqgM8v2JOkPJRA3UE_!!2-item_pic.png_400x400q60.jpg',
                'https://img.alicdn.com/bao/uploaded/i3/1917047079/O1CN01RAue1W22AENWLX18A_!!0-item_pic.jpg_400x400q60.jpg',
                'https://img.alicdn.com/bao/uploaded/bao/upload/O1CN01ToTLrs1de7pykr1zm_!!6000000003760-2-yinhe.png_400x400q60.jpg',
            ];
            $res = [
                'error' => 0,
                'msg' => 'ok',
                'data' => [
                    'info' => $getDetaols,
                ]
            ];
        }else{
            $res = [
                'error' => 400004,
                'msg' => 'Goods Not Exist',
            ];
        }
        return $res;
    }
    /**
     * 加入购物车
     */
    public function index(Request $request)
    {
        $goods_id = $request->get('goods_id');// 商品id
        $uid = $_SERVER['uid']; // 用户id
        // 查询商品的价格
        $shop_price = GoodsModel::find($goods_id)->shop_price;
        $goodsInfo = GoodsModel::where(['goods_id'=>$goods_id])->first();// 根据商品id查询一条数据
        // 判断加入的购物车商品是否存在
        $cart = CartModel::where(['goods_id'=>$goods_id])->first();
        if($cart){ // 增加商品数量
            CartModel::where(['goods_id'=>$goods_id])->increment('goods_num');
            $response = [
                'error'=>0,
                'msg'=>'ok',
            ];
        }else{
            // 将商品存入数据库或redis中
            $cartInfo = [
                'goods_id' => $goodsInfo['goods_id'],// 商品id
                'goods_name' => $goodsInfo['goods_name'],
                'goods_img' => $goodsInfo['goods_img'],
                'uid' => $uid, // 用户id
                'goods_num' => 1,
                'add_time' => time(),// 添加时间
                'is_delete' => 1,// 1 删除 2 不删除
                'shop_price' => $shop_price,
            ];
            $id = CartModel::insertGetId($cartInfo);// 加入小程序购物车
            if ($id) {
                $response = [
                    'error' => 0,
                    'msg' => "ok",
                ];
            } else {
                $response = [
                    'error' => 400004,
                    'msg' => "加入购物车失败",
                ];
            }
            return $response;
        }
    }
    /**
     * 购物车列表
     */
    public function list(Request $request){
        $uid = $_SERVER['uid'];
        $goods = CartModel::where(['uid'=>$uid])->get();
        if($goods){
            $goods = $goods->toArray();
            foreach ($goods as $k=>$v){
                $g = GoodsModel::find($v['goods_id']);
                $v['goods_name'] = $g->goods_name;
            }
        }else{
            $goods = [];
        }
        $response = [
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'list'=>$goods
            ]
        ];
        return $response;
    }
    /**
     * 加入收藏
     * @param Request $request
     */
    public function addFav(Request $request){
      $goods_id = $request->get('goods_id');// 商品id
      // 加入收藏至redis 有序集合
        $uid = $_SERVER['uid'];
        $redis_key = 'ss:goods:fav:'.$uid;// 用户收藏的商品到有序集合
        Redis::Zadd($redis_key,time(),$goods_id);// 添加至redis有序集合中
        $response = [
            'error'=>0,
            'msg'=>'ok',
        ];
        return $response;
    }
    /**
     * 商品数量增加
     */
    public function addCount(Request $request){
        $goods_id = $request->get('goods_id');// 商品id
        $uid = $_SERVER['uid'];// 用户id
        $cart = CartModel::where('goods_id',$goods_id)->first()->toArray();
        $goods_num = $cart['goods_num']+1;// 数量+1
        if($goods_num){
            $res = [
                'goods_num'=>$goods_num
            ];
            CartModel::where(['goods_id'=>$goods_id])->update($res);
        }
        $respose = [
            'error'=>0,
            'msg'=>'ok',
        ];
        return $respose;
    }
    /**
     * 商品数量减少
     */
    public function minusCount(Request $request){
        $goods_id = $request->get('goods_id');// 商品id
        $uid = $_SERVER['uid'];// 用户id
        $cart = CartModel::where('goods_id',$goods_id)->first()->toArray();
        $goods_num = $cart['goods_num']-1;// 数量+1
        if($goods_num){
            $res = [
                'goods_num'=>$goods_num
            ];
            CartModel::where(['goods_id'=>$goods_id])->update($res);
        }
        $respose = [
            'error'=>0,
            'msg'=>'ok',
        ];
        return $respose;
    }
    /**
     * 统计商品数量
     */
    public function goodsCount(Request $request){
        $uid = $_SERVER['uid'];// 用户id
        $goodsCount = CartModel::where('uid',$uid)->count();
        $response = [
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'goodsCount'=>$goodsCount
            ]
        ];
        return $response;
    }
    /**
     * 清空购物车
     */
    public function deleteList(Request $request){
        $uid = $_SERVER['uid'];// 用户id
        $deleteList = CartModel::where('uid',$uid)->first();
        $response = [
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'deleteList'=>$deleteList,
            ]
        ];
        CartModel::where('uid',$uid)->delete();
        return $response;
    }
    /**
     * 删除商品
     */
    public function delete(Request $request){
        $uid = $_SERVER['uid'];
        $goods_id = $request->post('goods');
        $goods_arr = explode(',',$goods_id);
        $delete = CartModel::whereIn('goods_id',$goods_arr)->delete();
//        $delete = CartModel::where(['uid'=>$uid])->first();
//        $goods_id = $delete['goods_id'];
        if(!empty($delete)){
            $response=[
                'error'=>'0',
                'msg'=>'ok',
                'data'=>$delete
            ];
//            CartModel::where(['goods_id'=>$goods_id])->delete();
//            return $response;
        }else{
            $response=[
                'error'=>'400004',
                'msg'=>'no',
            ];
            return $response;
        }
    }
}
?>

















