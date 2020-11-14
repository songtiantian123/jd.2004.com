<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use App\Model\UserModel;
use App\Model\GithubUserModel;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
class UserController extends Controller{
    /** 前台首页登录*/
    public function login(){
        return view('user.login');
    }
    /** 前台执行登录*/
    public function loginDo(Request $request){
        $ip = $request->getClientIp();// 得到当前真实的IP
        $user_name = $request->input('user_name');// 获取用户名
        $password = $request->input('password');// 获取密码
        $key = 'login:count：' . $user_name;// 当前用户登录次数
        $count = Redis::get($key);// 获取key
        $res = UserModel::where(['user_name' => $user_name])
            ->orWhere(['tel' => $user_name])
            ->orWhere(['email' => $user_name])
            ->first();
        if (empty($res)) {
            return redirect('/user/login')->with(['msg' => '账号或密码有误']);
        }
        if (is_object($res)) {
            $res = $res->toArray();
        }
        // 打印出用户剩余的时间 账号错误过多才会出现倒计时
        $login_time = ceil(Redis::TTL("login_time:".$res['uid']) / 60);
        if (!empty($login_time)) {
            return redirect('/user/login')->with(['msg' => '该账号输入错误过多,已锁定一小时，剩余时间:'.$login_time.'分钟']);
        }
        // dd($login_time);
        //判断用户是否锁定
        if ($count>=4) {
            Redis::setex("login_time:".$res['uid'],3600,Redis::get("login_time:".$res['uid']));
            return redirect('/user/login')->with(['msg' => '该账号输入错误次数过多,已锁定一小时']);
        }
        // 密码
        $user_name1 = $request->input('password');
        $user_name1 = md5($user_name1);
        if ($user_name1 == $res['password']) {
            $loginInfo = ['last_login' => time(), 'last_ip' => $ip, 'login_count' => $res['login_count'] + 1];
            UserModel::where('uid', $res['uid'])->update($loginInfo);
            // 登录成功后设置session存入用户的信息
            session(['uid' => $res['uid'], 'user_name' => $res['user_name'], 'tel' => $res['tel'], 'email' => $res['email']]);
        //echo "登录成功";
            //登录成功记录登录信息
            $key = "login:time".$res['uid'];
            Redis::rpush($key,time());
            return view('index/center');
        } else {
            $ten_minute = 10 * 60;
            if (time() > Redis::get('right_login' . $res['uid'])) {
                if (Redis::get('error_login', $res['uid'] > time() - $ten_minute)) {
                    if (Redis::get('error_login' . $res['uid']) >= 4) {
                        $right_time = time() + 3600;
                        Redis::set('right_login' . $res['uid'], $right_time);
                        return redirect('user/login')->with(['msg' => '你输入的账号或密码有误，错误次数，以达到5次，已锁定一小时']);
                    } else {
                        $ago_time = time() - 600;
                        // 错误次数
                        if (empty(Redis::get('error_login' . $res['uid']))) {//没有错误从0开始
                            $error_count = 1;
                            Redis::set('error_login' . $res['uid' . $error_count]);
                            Redis::set('error_time' . $res['uid'], time());
                        }else{// 已经错过 从错误的基础上再加1
                            $error_count = Redis::get('error_login' . $res['uid']);
                            Redis::set('error_login' . $res['uid'], $error_count + 1);
                        }
                    }
                } else {
                    Redis::set('error_time' . $res['uid'], time());
                }
            } else {
                return redirect('user/login')->with(['msg' => '你输入的密码已经错误5次,锁定一小时']);
            }
            // 密码不正确 记录错误次数
            $count = Redis::incr($key);
            Redis::expire($key, 600);
            return redirect('user/login')->with(['msg' => '你输入的账号或密码有误,错误次数: ' . $count . '最近错误的时间' . Redis::get('error_time' . $res['uid'])]);
            }
    }
    /** 前台首页注册*/
    public function register(){
        return view('user.register');
    }
    /** 前台执行注册*/
    public function registerDo(Request $request){
        $data = $request->except('_token');
        $data['reg_time']=time();// 注册时间
        //$data['password'] = md5($data['password']);//加密
        $uid = UserModel::insertGetId($data);
        // 生成激活码
        $active_code = Str::random(64);// 64位随机数
        // 保存激活码与用户对应关系 使用有序集合
        $redis_active_key = 'ss:user:active';
        Redis::zAdd($redis_active_key,$uid,$active_code);
        $active_url = env('APP_URL').'/user/active?code='.$active_code;
        echo $active_url;
        if($uid){
            return redirect('/user/login');
        }else{
            return redirect('user/register');
        }
    }
    /** 激活用户*/
    public function active(Request $request){
        $active_code = $request->get('code');
        echo "激活码:".$active_code;echo '</br>';

        $redis_active_key = 'ss:user:active';
        $uid = Redis::zScore($redis_active_key,$active_code);
        if($uid){
            echo "uid：".$uid;echo '</br>';
            // 激活用户
            UserModel::where(['uid'=>$uid])->update(['is_validated'=>1]);
            echo '激活成功';
            // 删除集合中的激活码
            Redis::zRem($redis_active_key,$active_code);
        }else{
            echo '激活码已失效';
        }
    }
    /** 退出*/
    public function exit(Request $request){
      session(['uid'=>null,'user_name'=>null,'tel'=>null,'email'=>null]);
      if(empty(session('uid'))){
          return redirect('user/login');
      }
    }
      /**第三方 github登录*/
      public function githublogin(Request $request){
            $code = $_GET['code'];// 接收code
            $token = $this->getAccessToken($code);// 换取access_token
             //获取用户信息
            $git_user = $this->getGithubUserInfo($token);
            //判断用户是否存在 不存在则入库新用户
             $u = GithubUserModel::where(['guid'=>$git_user['id']])->first();
             if($u){  // 存在
                 // TODO 登录逻辑
                 $this->webLogin($u->uid);
             }else{ // 不存在
                 // TODO 在用户表中创建新用户 获取uid
                 $new_user = [
                     'user_name' => Str::random(10)
                 ];
                 $uid = UserModel::insertGetId($new_user);
                 // 在github 用户表中记录新用户
                 $info = [
                     'uid' => $uid, // 新用户
                     'guid' => $git_user['id'], // github 用户id
                     'avatar' => $git_user['avatar_url'],
                     'github_url' => $git_user['html_url'],
                     'github_username' => $git_user['name'],
                     'github_email' => $git_user['email'],
                     'add_time' => time(),
                 ];
                 $guid = GithubUserModel::insertGetId($info);
                 // TODO 登录逻辑
                 $this->webLogin($uid);
             }
             // 将token返回客户端
             return redirect('/index/index');// 登录成功返回首页
    }
    /**获取access_token */
    protected function getAccessToken($code){
        $url = 'https://github.com/login/oauth/access_token';
        $client = new Client();
        $response = $client->request('GET',$url,[
            'verify' =>false,
            'form_params' => [
                'client_id' => env('OAUTH_GITHUB_ID'),
                'client_secret' => env('OAUTH_GITHUB_SEC'),
                'code' => $code
            ]
        ]);
        parse_str($response->getBody(),$str);// 返回字符串access_token=59a8a45407f1c01126f98b5db256f078e54f6d18&scope=&token_type=bearer
        return $str['access_token'];
    }
    /**获取github个人信息 */
    protected function getGithubUserInfo($token){
        $url = 'https://api.github.com/user';
        // GET请求接口
        $client = new Client();
        $response = $client->request('GET',$url,[
            'verify' => false,
            'headers' => [
                'Authorization' => "token $token",
            ],
        ]);
        return json_decode($response->getBody(),true);
    }
    /** WEB 逻辑登录 */
    protected function weblogin($uid){
        // 将登录信息保持至session中与token写入session
        session(['uid'=>$uid]);
    }
}
