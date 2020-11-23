<?php
namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Model\GoodsModel;
use App\Model\CartModel;
class CartController extends Controller{
    public function index(Request $request){
        $goods_id = $request->get('goods_id');
        $goodsInfo = GoodsModel::where('goods_id',$goods_id)->first();
        if($goodsInfo){
            $cartInfo = [
                'goods_id'=>$goodsInfo['goods_id'],
                'add_time'=>time(),
            ];
            CartModel::insert($cartInfo);
            return $cartInfo;
        }
    }
}
