<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\Xcx_CartModel;
use App\Model\GoodsModel;
use App\Model\SeatModel;
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
        echo '<pre>' . 'key: >>>>>' . $token_key;
        echo '</pre>';
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
        $goods_id = $request->get('goods_id');
        $goodsInfo = GoodsModel::where('goods_id',$goods_id)->first();
        if($goodsInfo){
            $cartInfo = [
                'goods_id'=>$goodsInfo['goods_id'],
                'add_time'=>time(),
            ];
            Xcx_CartModel::insert($cartInfo);
            return $cartInfo;
        }
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

















