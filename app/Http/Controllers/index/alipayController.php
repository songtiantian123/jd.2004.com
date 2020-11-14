<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Bus\DispatchesJobs;
// use Illuminate\Routing\Controller as BaseController;
use App\Model\CartModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\ShopModel;
use Illuminate\Http\Request;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
class alipayController extends Controller{
    /** 支付*/
    protected $config =[
        'app_id' => '2021000116699183',//你创建应用的APPID
        'notify_url' => 'http://2004.liliqin.xyz/alipay/AliPayReturn',// 异步回调地址
        'return_url' => 'http://2004.liliqin.xyz/alipay/AliPayNotify',// 同步回调地址
        'ali_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApxU0dF/pZUA3K5Bo66Hy92VUC2uHLVG2cXOD6v1n8rJwOipQwOZyW+oHQ2SSmdSNnwNMCrtmvW3do9s7XRu0XdK7leWz5vJpoLP/sT0rtC/mY3whNC/Jytg+Kd1zlQvyTU36+nsICcrHS6NSyyliWvtldQtIO98O6bYBJLg5hOHtxnAUUywZDF/bk8mTJ3tda6MKqMEacE6Gt6lLbp69m3R29TquQTIM7EQRFQm/S6C/Z06abCwWtW1HlRZgKnufWzeIsYYd3gsJNLRzPgALgT8oMHqboSMC+pQ8TeTMY5Z16n2f6fn1N5a087fO1N/zTR2yMJB1NU2Gx8WUa0TwOQIDAQAB",
        'private_key' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCIUebjPc5ozI+G2dM/5xpREoRqokyDsIXHfrvZnknL+4zdb9AbVuR8kNgbYDZ66D8WFp3O3nWN/RPpxGm9taB7WNHYxOmFoZShj6htvoMOuJn1/7zH/K9Vy3BKX/xhIVOlTKXGBcBE1PC+c6sMf0F9OyWc1rqb0wuH9H1bcBiu2y98aSygBBThtFgo4CkwbCxzo21TgQ/M27zUtmrDWxZKbKYg7fq6aWSfAUEXa1KmSt+j8I2cd8YjZc0Fp2o4f6jZ9RXKmmf9WhjA7NV5dEVTfmA48ka1sSVuDL3/3Re1fQtqFsHJKT7Fhd0y1IexQi7ihPGALOmtj1EiwOXCmBBlAgMBAAECggEAVbw9HHqI0Pym4EcrR6uPr6GXyoEc4k4pNVkNyEZ3X2VsKPDjz+4MchOe90jBuvdKhhffVHYxNQYQehvf7ULIFgu8nzrpK/R9yEbTxYHmZ2HO4xrenmDb3Xe/vec0kdomA3Z7ZFnFnQTYDNAuqgN3Ks4CT+v1HX/UZsNr/BA6Ve30G8A8W6R0mjA8ZW3vf/wLeka7Qsf2ff7iR06ppRsbbcmrPh1rlP5s/bqqzublG2iZTPidVePF9e2o0Ipp03QPeGlb5X5gdusZfWsCP0L4rBesLqebVECRWaLKxUVnSpG9nU5LEfA/fT4EL7ZMNIe30xbBy1Ey5PivkU8huxYvmQKBgQDRk9OcWxsgOBQpuqKkJKCgHCUwyVxn6IzwPjmb5+smOTZUhG5n4zq+A1nJgZtRsrMWM+bINApNEnRta19h3z1Uo2v+FEgvn4AvhWdamv6/Njr395Or8Y9WQsERnHTkX4LXr90r+g19VvncQScQ+9XPjv+saDpWy/3QxSP9s1LqPwKBgQCmg/pwobhlxWhw+d8It5pvmIg5+KoVGqtW7YIuRSd3RcMGrBVhwnZy26c3IaI3kXAh2JFaSr7DBvVIHKPdSIPo/EjlI07XezTfgMgzBRVK/hhQgL8lhJ7JeounxFuWxa4H3BZUdOsUCihF38MtbXbtvJe18ds1BXHsBmuV7PY0WwKBgCkdYCFHek3a0pHRLIEZMm3Wt7EXf8pew++JtZGRcP9hr/fqtyIoYOjQDXhLteXUMfAEJJ1YIEE4gqDItMClpAmLue7xmavGFca83CbZS2rFv9HPvye3TxB0Lh4/XGtFFY0s0i4Dc0wImSINohVh4nNCsYPoOrG2eUfQtRvbZ0PPAoGBAKQB/hwNzXe/9lzAX+NQI/aiwBqJR8y9leFq0fwM9RBPUAY0XGMLjGsY2hw9Lm+Y+l771j6evEGPiuvZ+bQshnBmfM3j9vXaTnuNdqJ58T0KBJzWEm87rsI3x3IYvzVDw2PObNgGyLvWPVCFUtJdrPP/+1WjwAr7L/gPYswqt11dAoGBALgDTK2wDmmV7fC1Ci4fVyje2TyxzOOmw6AlVq7L9BMKlKFAVIw7kw7hdxSNvxivy7ht877kBa5mFrPFSlS6CpFPtG273nwUP+LveRfqKw3doOXqdwWvDtpEa2lq7I4xtWrafuREajdWgOpMidSTw7qWIP1HKmek1YBoH4b0Bh47',// 密钥
        'log' =>[
            'file' => './logs/alipay.log',
            'level' => 'info',
            'type' =>'single',
            'max_file' =>30,
        ],
        'http' => [
          'timeout' =>5.0,
          'connect_timeout' => 5.0,
        ],
        'mode'=>'dev',
    ];
    public function Alipay($order){
        $uid = session('uid');
        $alipay = Pay::alipay($this->config)->web($order);
        // TODO 清空购物车
        CartModel::where(['uid'=>$uid])->delete();
        return $alipay;
    }
    /** 异步回调*/
    public function AliPayReturn(){
        $data = Pay::alipay($this->config)->verify();
        return Pay::alipay($this->config)->success();
    }
    /** 同步回调*/
    public function AliPayNotify(Request $request){
        $data = Pay::alipay($this->config)->verify();
        $money = $data->total_amount;
        return view('/orders/paysucces',['money'=>$money]);
    }
}
