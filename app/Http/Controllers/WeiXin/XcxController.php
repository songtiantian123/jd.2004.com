<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\Wx_UserModel;
class XcxController extends Controller{
    /**
     * 小程序登录
     */
    public function login(Request $request){
      $code = $request->get('code');
      $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.env('WX_XCX_APPID').'&secret='.env('WX_XCX_SECRET').'&js_code='.$code.'&grant_type=authorization_code';
      $data = json_decode(file_get_contents($url),true);
      $openid = $data['openid'];
//      echo 'openid：'.$openid;die;
//      echo '<pre>';print_r($data);echo '</pre>';die;
      // 自定义登录状态
        if(isset($data['errcode'])){// 错误
            $response = [
                'error'=>5000001,
                'msg'=>'登录失败',
            ];
        }else{// 成功
            $res = Wx_UserModel::where('openid',$openid)->first();
            if(empty($res)){
                $token = sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
                // 保存token到redis中
                $redis_key = 'xcx_token:'.$token;
                Redis::set($redis_key,time());
                // 设置过期时间
                Redis::expire($redis_key,7200);
                $response = [
                    'openid'=>$openid,
//                'error'=>0,
//                'msg'=>'登录成功',
//                'data'=>[
//                    'token'=>$token,
//                ],
                ];
                Wx_UserModel::insert($response);
            }else{
                $response = [
                    'error'=>400001,
                    'msg'=>'已存在',
                ];
            }

        }
        return $response;
    }
}





























