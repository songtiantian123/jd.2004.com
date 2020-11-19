<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\GoodsModel;
use App\Model\SeatModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\TicketModel;
use Illuminate\Http\Request;
class ApiController extends Controller{
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
    public function goodslist(Request $request){
        //$res = GoodsModel::select('goods_id','goods_name','shop_price','goods_img')->limit(10)->get()->toArray();
        $page_size = $request->get('ps');
        $res = GoodsModel::select('goods_id','goods_name','shop_price','goods_img')->paginate($page_size);
        $respons = [
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'list'=>$res->items()
            ],
        ];
        return $respons;
    }
    /**
     * 商品详情(一件商品的详细信息)
     */
    public function getDetails(Request $request){
        $goods_id = $request->get('goods_id');
        $getDetaols = GoodsModel::where('goods_id',$goods_id)->first();
        $res = [
           'error'=>0,
           'msg'=>'ok',
           'data'=>[
               'res'=>$getDetaols,
           ]
        ];
        return $res;
    }
}





























