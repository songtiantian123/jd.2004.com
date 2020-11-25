<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\Xcx_CartModel;
use App\Model\GoodsModel;
use App\Model\Wx_UserModel;
use App\Model\Xcx_UserModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\TicketModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

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
        $token = $request->get('access_token');
        // 验证token是否有效
        $token_key = 'h:xcx:login:' . $token;
        echo '<pre>' . 'key: >>>>>' . $token_key;echo '</pre>';
        $res = Redis::get($token_key);
//        echo $res;die;
        // 检查token是否存在
        $status = Redis::exists($token_key);
//        dd($status);
        if ($status == 0) {
            $reponse = [
                'error' => 400004,
                'msg' => '未授权'
            ];
            return $reponse;
        }
        $goods_id = $request->get('goods_id');
        $getDetaols = GoodsModel::find($goods_id);
        $res = [
            'error' => 0,
            'msg' => 'ok',
            'data' => [
                'res' => $getDetaols,

            ]
        ];
        return $res;
    }
    /**
     * 加入购物车
     */
    public function index(Request $request){
        $token = $request->get('access_token');// 获取access_token
        $key= "h:xcx:login:".$token;
        $token1 = Redis::hgetall($key);// 查询出用户信息
        $token = $token1['uid'];// 用户id
        $user_id = Xcx_UserModel::where('openid',$token['openid'])->select('uid')->first();// 根据用户id查询小程序用户表
        $goods_id = $request->get('goods_id');// 商品id
        $goodsInfo = GoodsModel::where('goods_id',$goods_id)->first();// 根据商品id查询一条数据
        if($goodsInfo){
            $cartInfo = [
                'goods_id'=>$goodsInfo['goods_id'],// 商品id
                'goods_name'=>$goodsInfo['goods_name'],// 商品名称
                'add_time'=>time(),// 添加时间
                'uid'=>2, // 用户id
                'is_delete'=>1,// 1 删除 2 不删除
            ];
            $res = Xcx_CartModel::insert($cartInfo);// 加入小程序购物车
            if($res){
                $response=[
                    'error'=>0,
                    'msg'=>"加入购物车成功",
                ];
            }else{
                $response=[
                    'error'=>400004,
                    'msg'=>"加入购物车失败",
                ];
            }
            return $response;
        }
    }
    /**
     * 购物车列表
     */
    public function list(Request $request){
      $res = Xcx_CartModel::get();
      dd($res);
    }
}





/**
//                   'goods_img'=>[
//                   '/images/pet.jpg',
//                   '/images/rabbit.jpg',
//                   '/images/pet.jpg',
//                   '/images/rabbit.jpg',
//               ],
 */






?>

















