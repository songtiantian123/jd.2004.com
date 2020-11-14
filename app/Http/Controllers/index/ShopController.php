<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\ShopModel;
use Illuminate\Http\Request;
class ShopController extends Controller{
    public function create(){
        return view('shop.create');
    }
    public function aa(){
        echo '测试';
    }
    /***图片上传 */
    public function uploadImg(){
//        echo storage_path('img/aaa.jpg');die;
        return view('shop.upload');
    }
    /**处理文件上传 */
    public function uploadImg1(Request $request){
        $file = $request->file('img');
        $name = $file->getClientOriginalName();// 原名称
        $text = $file->getClientOriginalExtension(); // 获取扩展
        $size = $file->getSize();
        //echo 'size'.$size;
        // 保存
//        $file->storeAs($name,$text,$size);
        $path = 'public/img';
        $name = 'aaa.'.$text;
        $res = $file->storeAs($path,$name);
        var_dump($res);
    }

}
