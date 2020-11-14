<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uid = session('uid');
        $sess = session()->all();
        if(empty($uid)){// 0 false [] {{}} "" null
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHTTPREQUEST'){
                $response = [
                    'error'=>400003,
                    'msg'=>'请先登录',
                ];
                die(json_encode($response));die;
            }
            //return redirect('/user/login');
        }
        return $next($request);
    }
}
