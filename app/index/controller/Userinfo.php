<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/3
 * Time: 10:30
 */

namespace app\index\controller;

use app\admin\help\Result;
use app\ApiController;
use app\Request;
use think\db\Query;
use think\facade\Cache;
use think\facade\Config;
use  think\facade\Db;

class Userinfo extends ApiController
{
    protected $middleware = ['\app\middleware\Check::class'];
    protected $user;

    //个人信息
    public function users(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();

        return Result::Success($user);
    }

    //版本更新
    public function version()
    {

//        return Result::Success([ 'link' => 'https://' . $_SERVER['HTTP_HOST'] . '/' . '图钉1.1.1.apk', 'version_number' => '1.1.1']);
        return Result::Error('1000','无更新');
    }


    public function autonym(Request $request)
    {
        $user = $this->user($request);
        try{
            $req = $request->param();
            $host = "https://jmbank.market.alicloudapi.com";
            $path = "/bankcard/validate";
            $method = "POST";
            $appcode = "97c7994d45da4d5083999461c8bdc1ea";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            array_push($headers, "Content-Type" . ":" . "application/x-www-form-urlencoded; charset=UTF-8");
            $querys = "";
            $bodys = "bankcard_number=" . $req['bankcard_number'] . "&idcard_number=" . $req['idcard_number'] . "&mobile_number=" . $req['mobile_number'] . "&name=" . urlencode($req['name']);
            $url = $host . $path;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            if (1 == strpos("$" . $host, "https://")) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);

            $data = curl_exec($curl); // 运行curl
            curl_close($curl);
            $da = json_decode($data, true);
            if ($da['code'] == 200) {
                $user->name = $req['name'];
                $user->card_number = $req['idcard_number'];
                $user->bank_number = $req['bankcard_number'];
                $user->bank = $da['data']['bank_info']['bank'];
                $user->save();
            }
            return $data;
        }catch(\Exception $e){
            return json(['code'=>'1000','msg'=>'信息有误']);
        }

    }


}