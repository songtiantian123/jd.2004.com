<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\ShopModel;
use Illuminate\Http\Request;
class HomeController extends Controller{
    /** 前台首页*/
    public function home(){
        return view('index.home');
    }
}
