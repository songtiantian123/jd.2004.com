<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    //phpinfo();
    return view('welcome');
});
Route::get('/text/weather','index\TextController@weather');//天气get
Route::get('/text/weather2','index\TextController@weather2');//天气post
Route::get('/text/curl1','index\TextController@curl1');//天气2
Route::get('/text/guzzleHttp','index\TextController@guzzleHttp');//guzzleHttp get post请求
Route::get('/github/callback','index\UserController@githublogin');//GITHUB登录

//$router->resource('user-models', UserController::class);

Route::get('shop/create','index\ShopController@create');
Route::get('/shop/uploadImg','index\ShopController@uploadImg');
Route::post('/shop/uploadImg1','index\ShopController@uploadImg1');

Route::get('/test','index\TestController@test');




/** 品优购 登录和注册 退出*/
Route::get('/','index\IndexController@index');// 前台首页
Route::get('/user/login','index\UserController@login');// 前台登录
Route::post('/user/loginDo','index\UserController@loginDo');// 前台执行登录
Route::post('/index/center','index\UserController@center');// 用户中心
Route::get('/user/exit','index\UserController@exit');// 退出
Route::get('/user/register','index\UserController@register');// 前台注册
Route::post('/user/registerDo','index\UserController@registerDo');// 前台执行注册
Route::get('/index/details','index\IndexController@details');// 前台查询商品


/**
 * 邮件激活  用户
 */
Route::get('/user/active','index\UserController@active');//邮件激活
Route::get('/hello','index\UserController@hello');// 测试  控制器已删除

/** 商品列表*/
Route::get('/goods/list','index\GoodsController@list');//商品列表
Route::get('/goods/details','index\GoodsController@details');//商品详情+缓存
Route::get('/goods/cache','index\GoodsController@cache');//商品缓存
Route::get('/goods/collect','index\GoodsController@collect');//商品收藏
Route::post('/goods/comment','index\GoodsController@comment');//商品评价



/** 加入购物车*/
Route::get('/cart/add','index\CartController@add');//加入购物车添加
Route::get('/cart/index','index\CartController@index');//购物车列表页面
Route::get('/cart/success','index\CartController@success');//添加到购物车
Route::get('/cart/getOrderInfo','index\CartController@getOrderInfo');//生成订单
Route::get('/cart/delete','index\CartController@delete');//删除购物车
Route::get('/cart/browse','index\CartController@browse');//浏览购物车



// 个人中心
Route::get('/index/center','index\IndexController@center');//个人中心页面
Route::get('/index/collect','index\IndexController@collect');//我的收藏
Route::get('/index/sign','index\IndexController@sign');// 用户签到
Route::get('/index/signDo','index\IndexController@signDo');// 用户签到处理

// 订单
Route::get('/orders/add','index\OrdersController@add');//添加订单
Route::get('/orders/orders','index\OrdersController@orders');//订单页面
Route::get('/orders/orderDetail','index\CartController@orderDetail');//生成订单


// 支付
Route::get('/orders/pay','index\OrdersController@pay');//支付宝支付
// 支付宝支付处理路由
Route::prefix('Alipay')->group(function (){
    // 发起支付请求
    Route::get('/','index\AlipayController@Alipay');
    // 服务器同步通知页面路径
    Route::get('AliPayNotify','index\AlipayController@AliPayNotify');
    // 页面跳转异步通知页面路径
    Route::get('AliPayReturn','index\AlipayController@AliPayReturn');
});


/** 抽奖*/
Route::get('/prize','index\PrizeController@index');//抽奖页面
Route::get('/prize/start','index\PrizeController@add');//开始抽奖

/** 购票*/
Route::get('/film','index\TicketController@film');//购票
Route::post('/film/filmadd','index\TicketController@filmadd');//开始购票

/** 优化卷*/
Route::get('/coupon','index\CouponController@coupon');//优惠卷页面
Route::post('/coupon/receive','index\CouponController@receive')->middleware('check.login');//领取优惠卷



Route::get('/text','TextController@text');// redis测试
Route::get('/text1','TextController@text1');// 测试1
Route::get('/text2','TextController@text2');// 测试2
Route::post('/text3','TextController@text3');// 测试3


// 微信
Route::prefix('/wx')->group(function (){
    //Route::post('/wx','WeiXinController@checkSignature');// 微信接口
    Route::match(['get','post'],'/','index\WeiXinController@wxEvent');// 接收事件推送
    Route::get('/token','index\WeiXinController@getAccessToken');// 获取access_token
    Route::get('/create_menu','index\WeiXinController@createMenu');// 创建菜单
    Route::get('/check','index\WeiXinController@check');// 验证签名
    Route::get('/authoriz','index\WeiXinController@index');// 微信网页授权
    Route::get('/auth','index\WeiXinController@jump');// 微信网页授权
    Route::post('/xcxlogin','WeiXin\XcxController@login');// 微信小程序登录 获取code
});


// text 路由分组
Route::prefix('/text')->group(function(){
    Route::get('/guzzle1','TextController@guzzle1');// guzzle get请求
    Route::get('/guzzle2','WeiXinController@guzzle2');// guzzle post请求
    Route::get('/media','WeiXinController@dlMedia');// 下载素材图片
    Route::get('/voice','WeiXinController@vic');// 下载素材音频
    Route::get('/video','WeiXinController@vid');// 下载素材视频
});

// 小程序接口
Route::prefix('/api')->group(function(){
    Route::get('/details','WeiXin\ApiController@goodslist');    // 列表页
    Route::get('/getDetails','WeiXin\ApiController@getDetails');// 详情页
    Route::post('/addCart','WeiXin\ApiController@index');// 加入购物车
    Route::post('/list','WeiXin\ApiController@list');// 加入购物车
});
?>

