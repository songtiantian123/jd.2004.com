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
use App\Model\Xcx_UserModel;
class XcxController extends Controller{
    /**
     * 小程序登录
     */
    public function login(Request $request){
        // 获取code
      $code = $request->get('code');
        // 获取用户信息
        $userinfo = json_decode(file_get_contents("php://input"),true);
        // 使用code
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.env('WX_XCX_APPID').'&secret='.env('WX_XCX_SECRET').'&js_code='.$code.'&grant_type=authorization_code';
      // 获取用户信息 openid 和 session_key
      $data = json_decode(file_get_contents($url),true);
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
                // 旧用户
                $uid = $user->id;
            }else{
                // 新用户
                $u_info = [
                    'openid'=>$openid, //
                    'nickname'=>$userinfo['u']['nickName'],
                    'avatarUrl'=>$userinfo['u']['avatarUrl'],
                    'sex'=>$userinfo['u']['gender'],
                    'language'=>$userinfo['u']['language'],
                    'city'=>$userinfo['u']['city'],
                    'province'=>$userinfo['u']['province'],
                    'country'=>$userinfo['u']['country'],
                    'add_time'=>time(), // 添加时间
                    'type'=>3, // 小程序
                    'update_time'=>time()
                ];
                $uid = Xcx_UserModel::insertGetId($u_info);
            }
            // 生成token
            $token = sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
            $redis_login_hash = 'h:xcx:login:'.$token;//保存token
            $login_info= [
                'uid'=>$uid, // 用户id
                'user_name'=>'天', // 用户名
                'login_time'=>date('Y-m-d H:i:s'),// 用户登录事件
                'login_ip'=>$request->getClientIp(),// 用户IP
                'token'=>$token, // token
                'openid'=>$openid
            ];
            // 保存登录信息
            Redis::hMset($redis_login_hash,$login_info);
            // 设置过期时间 2小时
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
    /**
     * 个人中心登录
     */
    public function UserLogin(Request $request){
       $token = $request->get('token');// 接收token
       // 获取用户信息
       $userinfo = json_decode(file_get_contents("php://input"),true);
       $redis_login_hash = 'h:xcx:login:'.$token;
       $openid = Redis::hget($redis_login_hash,'openid');
       $u = Xcx_UserModel::where(['openid'=>$openid])->first();
       if($u['update_time']==0){
           // 因为用户已经登录过 所以只更新用户信息表
           $u_info = [
               'nickname'=>$userinfo['u']['nickName'],
               'avatarUrl'=>$userinfo['u']['avatarUrl'],
               'sex'=>$userinfo['u']['gender'],
               'language'=>$userinfo['u']['language'],
               'city'=>$userinfo['u']['city'],
               'province'=>$userinfo['u']['province'],
               'country'=>$userinfo['u']['country'],
               'update_time'=>time()
           ];
           Xcx_UserModel::where(['openid'=>$openid])->update($u_info);
       }
       $response = [
           'error'=>0,
           'msg'=>'ok'
       ];
       return $response;
    }
}





























