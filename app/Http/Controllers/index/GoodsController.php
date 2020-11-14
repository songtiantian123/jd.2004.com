<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\BrowseModel;
use App\Model\CollectModel;
use App\Model\CommentModel;
use App\Model\UserModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\GoodsModel;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redis;

class GoodsController extends Controller
{
    /** 商品详情页*/
    public function details(Request $request){
        $uid = session('uid');// 用户id
        $goods_id = $request->get('id');// 商品id
        // 加入历史浏览记录
        if (!empty($uid)) {
            $user_ip = $request->getClientIp();
            $data = [
                'goods_id' => $goods_id,// 商品id
                'user_id' => $uid,// 用户id
                'browse_time' => time(),// 浏览时间
                'browse_ip' => $user_ip,// 用户IP
            ];
            $browse_time = BrowseModel::where([['user_id', '=', $uid], ['goods_id', '=', $goods_id], ['browse_ip', '=', $user_ip]])
                ->orderBy('browse_time', 'desc')
                ->select('browse_time', 'browse_ip', 'browse_id')
                ->first();
            // 是对象类型转换成数组
            if (is_object($browse_time)) {
                $browse_time = $browse_time->toArray();
            }
            if (!empty($browse_time)) {
                if (time() - 60 > $browse_time['browse_time']) {
                    $res = BrowseModel::insert($data);
                } else {
                    BrowseModel::where([['user_id','=',$uid],['goods_id','=',$goods_id],['browse_ip','=',$user_ip],['browse_id','=',$browse_time['browse_time']]])->update(['browse_time' => time()]);
                }
            } else {
                $res = BrowseModel::insert($data);
            }
        }
        $key = 'h:goods_info:' . $goods_id;
        $goods_id = $request->get('id');
        // 查询缓存
        $g = Redis::hGetAll($key);
        if ($g) {// 有缓存
            echo '有缓存,不用查询数据库';
        } else {
            echo '无缓存,正在查询数据库';
            // 获取商品信息
            $goods_info = GoodsModel::find($goods_id);
            // 验证商品是否失效 (是否存在 是否下架 是否删除)
            if (empty($goods_info)) {
                return view('goods.404'); // 商品不存在
            }
            // 是否删除
            if ($goods_info->is_delete == 1) {
                return view('goods.delete'); // 商品已删除
            }
            $g = $goods_info->toArray();
            // 存入缓存
            Redis::hMset($key, $g);
            echo "数据缓存Redis中";
        }
        //echo '<pre>';print_r($g);echo '</pre>';
        $data = [
            'goods' => $g
        ];
        $goods = GoodsModel::first();
        // 查询用户是否收藏
        $Collect = CollectModel::where('user_id', $uid)->first();
        if (!empty($Collect)) {
            $Collect = 1;// 不收藏
        } else {
            $Collect = 2; // 收藏
        }
        // 查询用户是否评论
        $comment = CommentModel::where('goods_id', $goods_id)->get();
        if (is_object($comment)) {
            $comment = $comment->toArray();
        }
        if (!empty($comment)) {
            foreach ($comment as $k => $v) {
                $user = UserModel::where('uid', $v['user_id'])->value('user_name');
                $v['user_name'] = $user;
                $comment[] = $v;
            }
        }
        if (empty($comment)) {
            $comment = [];
        }
        // 商品浏览量+1
        GoodsModel::where(['goods_id'=>$goods_id])->increment("click_count");
        return view('/goods/details', $data, ['goods' => $goods, 'Collect' => $Collect]);
    }
    /** 商品列表*/
    public function list(Request $request){
        // 查询商品表中的数据
       $goods = GoodsModel::limit(10)->get();// 查询商品表10条数据
       $goods_name = $request->goods_name;// 接收搜索的值
       $search = GoodsModel::where('goods_name','like',"%".$goods_name."%")->paginate(12);
       if(is_object($search)){
           $search = $search->toArray();
       }

       $page = isset($page)?$request['page']:1;
//       $list = $search->appends(array(
//          'goods_name' =>$goods_name,
//          'page' =>$page,
//       ));

       Paginator::defaultSimpleView('pagination::bootstrap-4');
       return view('/goods/list',['goods'=>$goods,'search'=>$search,'goods_name'=>$goods_name]);
    }
    /** 商品收藏*/
    public function collect(Request $request){
        $uid = session('uid');// 用户id
        if(empty($uid)){
            $res =[
                'error' => 400,
                'msg' => '请先登录'
            ];
            return json_encode($res,true);
        }
        $where = ['user_id'=>$uid];
        $goods_id = $request->get('id');// 接收商品id
        //添加数据
        $data = [
            'goods_id' =>$goods_id,
            'user_id' =>$uid,
            'add_time' =>time(),
            'is_collect' =>2,// 1 不收藏 2 收藏
        ];
        // 入库
        $res = CollectModel::where($where)->first();
        if(empty($res)){
            CollectModel::insert($data);
            $data = [
                'error' =>200,
                'msg' =>'收藏成功',
            ];
            // 收藏+1
            GoodsModel::where(['goods_id'=>$goods_id])->increment("fav_count");
            return json_encode($data,true);
        }else{
            CollectModel::where($where)->delete();
            $data = [
                'error' =>204,
                'msg' =>'取消收藏',
            ];
            // 收藏排行+1
            //GoodsModel::where(['goods_id'=>$goods_id])->increment("fav_count");
            return json_encode($data,true);
        }
    }
    /** 商品评论*/
    public function comment(Request $request){
        $goods_id = $request->get('id');// 获取商品id
        $comment_value = $request->comment_value;
        $uid = session('uid');
        if(empty($uid)){
            $res = [
                'error' =>400,
                'msg' =>'请先登录',
            ];
            return json_encode($res,true);
        }
        $where = ['user_id',$uid];
        $data = [
            'goods_id' => $goods_id,
            'user_id' => $uid,
            'comment_value' => $comment_value,
            'comment_time' => time(),
        ];
        $res = CommentModel::insert($data);
        if($res){
            $data = [
                'error' =>200,
                'msg' => '评论成功',
            ];
            return json_encode($data,true);
        }else{
            $data = [
                'error' =>204,
                'msg' => '评论失败',
            ];
            return json_encode($data,true);
        }
    }
    /** 商品详情页*/
    // public function details(Request $request){
    //     // 查询商品表
    //     $goods = GoodsModel::first();
    //     return view('/goods/details',['goods'=>$goods]);
    // }
}
