<?php

namespace App\Http\Controllers\WeiXin;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\SeatModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\TicketModel;
use Illuminate\Http\Request;
class XcxController extends Controller{
    /**
     * 小程序登录
     */
    public function login(Request $request){
      $code = $request->get('code');
      $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.env('WX_XCX_APPID').'&secret='.env('WX_XCX_SECRET').'&js_code='.$code.'&grant_type=authorization_code';
      echo $url;
    }
}





























