<?php


//namespace App\Http\Controllers\WeiXin;
//
//use App\Http\Controllers\Controller;
//use App\Model\GoodsModel;
//use Illuminate\Foundation\Bus\DispatchesJobs;
//use Illuminate\Routing\Controller as BaseController;
//use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Redis;
//use App\Model\Wx_UserModel;
//
//class XcxController extends Controller
//{
//    /**
//     * 小程序登录
//     */
//    public function login(Request $request)
//    {
//        $code = $request->get('code');
//        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . env('WX_XCX_APPID') . '&secret=' . env('WX_XCX_SECRET') . '&js_code=' . $code . '&grant_type=authorization_code';
//        // 获取用户信息
//        $data = json_decode(file_get_contents($url), true);
//        $openid = $data['openid'];
////      echo 'openid：'.$openid;die;
////      echo '<pre>';print_r($data);echo '</pre>';die;
//        // 自定义登录状态
//        if (isset($data['errcode'])) {// 错误
//            $response = [
//                'error' => 5000001,
//                'msg' => '登录失败',
//            ];
//        } else {// 成功
//            Wx_UserModel::insert(['openid' => $data['openid']]);
//            $token = sha1($data['openid'] . $data['session_key'] . mt_rand(0, 999999));
//            // 保存token到redis中
//            $redis_key = 'xcx_token:' . $token;
//            Redis::set($redis_key, time());
//            // 设置过期时间
//            Redis::expire($redis_key, 7200);
//            $response = [
//                'error' => 0,
//                'msg' => 'ok',
//                'data' => [
//                    'token' => $token,
//                ]
//            ];
//        }
//        return $response;
//    }
//}

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
        // 获取code
      $code = $request->get('code');
//      echo $code;die;
        // 获取用户信息
        $userinfo = json_decode(file_get_contents("php://input"),true);
        // 使用code
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.env('WX_XCX_APPID').'&secret='.env('WX_XCX_SECRET').'&js_code='.$code.'&grant_type=authorization_code';
//      echo $url;die;
      // 获取用户信息
      $data = json_decode(file_get_contents($url),true);
//      echo '<pre>';print_r($data);echo '</pre>';die;
//        dd($data);
      // 自定义登录状态
        if(isset($data['errcode'])){// 错误
            $response = [
                'error'=>5000001,
                'msg'=>'登录失败',
            ];
        }else{// 成功
            $openid = $data['openid'];// 用户openid
            // 判断 新用户 或 旧用户
            $user = Wx_UserModel::where(['openid'=>$openid])->first();
            if($user){
//                echo '旧用户';
            }else{
                $u_info = [
                    'openid'=>$openid,
                    'nickname'=>$userinfo['u']['nickName'],
                    'avatarUrl'=>$userinfo['u']['avatarUrl'],
                    'sex'=>$userinfo['u']['gender'],
                    'language'=>$userinfo['u']['language'],
                    'city'=>$userinfo['u']['city'],
                    'province'=>$userinfo['u']['province'],
                    'country'=>$userinfo['u']['country'],
                    'add_time'=>time(),
                    'type'=>3 // 小程序
                ];
                Wx_UserModel::insertGetId($u_info);
                // 生成token
                $token = sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
                // 保存token
                $redis_login_hash = 'h:xcx:login:'.$token;
                echo $redis_login_hash;
                $login_info= [
                    'uid'=>1234,
                    'user_name'=>'张三',
                    'login_time'=>date('Y-m-d H:i:s'),
                    'login_ip'=>$request->getClientIp(),
                    'token'=>$token
                ];
                // 保存登录信息
                Redis::hMset($redis_login_hash,$login_info);
                // 设置过期时间
                Redis::expire($redis_login_hash,7200);
                $response = [
                    'error'=>0,
                    'msg'=>'ok',
                    'data'=>[
                        'token'=>$token,
                    ]
                ];
                return $response;
            }
        }
    }
}





























