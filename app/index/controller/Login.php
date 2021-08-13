<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/19
 * Time: 18:20
 */

namespace app\index\controller;


use app\admin\help\Result;
use app\admin\model\SystemAdmin;
use app\ApiController;
use app\BaseController;
use app\Request;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Validate;
use think\Service;


class Login extends BaseController
{

    //注册
    public function register(\app\Request $request)
    {

        $post = $request->post();
        $validate = new \think\Validate();
        $validate->rule([
//            'name|登陆名' => 'require|unique:system_admin',
            'password|密码' => 'require',
            'phone|手机号' => 'require|number|unique:system_admin',
            'pushing_code|邀请码' => 'require',
            'code|验证码' => 'require',
        ]);
        if (!$validate->check($post)) {
            return Result::Error('1000', $validate->getError());
        }

        $num = Cache::get($post['phone']);
        if ($num != $post['code']) {
            return Result::Error('1000', '验证码错误,或已超时');
        }
        $str = md5(time());
        $token = substr($str,5,7);
        $user = [
//            'name' => $post['name'],
            'password' => password($post['password']),
            'phone' => $post['phone'],
//                'pushing_code'=>$post['pushing_code'],
            'auth_ids' => 8,//默认为下级代理商
            'pushing_code' => $token,//默认为下级代理商
        ];


        $highercount = SystemAdmin::where('pushing_code', '=', $post['pushing_code'])->count();
        $higher = SystemAdmin::where('pushing_code', '=', $post['pushing_code'])->find();

        if ($highercount <= 0) {
            return Result::Error('1000', '推荐码不存在');
        }
        $user['appid'] = $post['pushing_code'] . '_' . $highercount;
        $user['higher_level_id'] = $higher['id'];



        $res = SystemAdmin::create($user);
        if ($res) {
            return Result::Success($res, '注册成功');
        } else {
            return Result::Error('1000', '注册失败');
        }

    }

    //登陆
    public function login(Request $request)
    {
        $post = $request->post();
        $validate = new \think\Validate();
        $validate->rule([
            'username|登陆名' => 'require',
            'password|密码' => 'require',
        ]);
        if (!$validate->check($post)) {
            return Result::Error('1000', $validate->getError());
        }
        $post['phone'] = $post['username'];
        $user = SystemAdmin::where('phone', $post['phone'])->find();
        if ($user['phone'] != $post['phone'] || $user['password'] != password($post['password'])) {
            return Result::Error('1000', '账号或密码错误');
        }
        $user->token = md5(time());
        $user->save();
        return Result::Success($user, '登陆成功');

    }


    //生成二维码
    public function view($url = "http://www.baidu.com")
    {
        require '../app/admin/help/phpqrcode/phpqrcode.php';
        $value = $url;                    //二维码内容

        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 5;            //生成图片大小

        //生成二维码图片
        $filename = 'qrcode/' . microtime() . '.png';
        \QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

        $QR = $filename;                //已经生成的原始二维码图片文件


        dump($_SERVER);

        //生成二维码图片
        $filename = $_SERVER['DOCUMENT_ROOT'] . 'phpqrcode\images' . microtime() . '.png';

        \QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        $QR = $filename;        //已经生成的原始二维码图片文件
        dump($filename);
        $QR = imagecreatefromstring(file_get_contents($QR));
        dump($QR);
        //输出图片
        imagepng($QR, 'qrcode.png');
        imagedestroy($QR);
        return '<img src="qrcode.png" alt="使用微信扫描支付">';

    }

    public function code($url = "http://www.baidu.com")
    {
        require '../app/admin/help/phpqrcode/phpqrcode.php';
//        $qrcode = new \QRcode();
        $value = $url;                    //二维码内容
        $errorCorrectionLevel = 'H';    //容错级别
        $matrixPointSize = 6;           //生成图片大小
        ob_start();
        \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, 2);
        // $object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2); //这里就是把生成的图片流从缓冲区保存到内存对象上，使用base64_encode变成编码字符串，通过json返回给页面。
        $imageString = base64_encode(ob_get_contents()); //关闭缓冲区
        ob_end_clean(); //把生成的base64字符串返回给前端
        $data = array('code' => 200, 'data' => $imageString);
//        return '<img src="data:image/png;base64,'.$imageString.'" >';
        return json($data);

    }

    //发送短信
    public function sms(Request $request)
    {
        $phone = input('post.phone');
        if (empty($phone) || !validatePhone($phone)) {
            return json_encode(['code' => 100, 'msg' => '请输入正确的手机号!']);
        }
        $sign = Config::get('alisms.SignName');
        $code = Config::get('alisms.TemplateCode');
        $ak = Config::get('alisms.AccessKeyId');
        $sk = Config::get('alisms.Secret');
        $num = mt_rand(1000, 9999);
        // 请求的参数
        $params = [
            'phone' => $phone,
            'sign' => $sign,
            'code' => $code,
            'param' => json_encode([
                'code' => $num,
            ])
        ];
        $res = send_sms($ak, $sk, $params);
        if ($res['Code'] === 'OK') {
            Cache::set($phone, $num, 120);
            return Result::Success('验证码发送成功');
        } else {
            return Result::Error('1000', '验证码发送失败,请稍后再试');
        }
    }

}