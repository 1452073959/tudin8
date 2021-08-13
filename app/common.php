<?php
// 应用公共文件

use app\common\service\AuthService;
use think\facade\Cache;

if (!function_exists('__url')) {

    /**
     * 构建URL地址
     * @param string $url
     * @param array $vars
     * @param bool $suffix
     * @param bool $domain
     * @return string
     */
    function __url(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        return url($url, $vars, $suffix, $domain)->build();
    }
}

if (!function_exists('password')) {

    /**
     * 密码加密算法
     * @param $value 需要加密的值
     * @param $type  加密类型，默认为md5 （md5, hash）
     * @return mixed
     */
    function password($value)
    {
        $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
        return sha1($value);
    }

}

if (!function_exists('xdebug')) {

    /**
     * debug调试
     * @param string|array $data 打印信息
     * @param string $type 类型
     * @param string $suffix 文件后缀名
     * @param bool $force
     * @param null $file
     */
    function xdebug($data, $type = 'xdebug', $suffix = null, $force = false, $file = null)
    {
        !is_dir(runtime_path() . 'xdebug/') && mkdir(runtime_path() . 'xdebug/');
        if (is_null($file)) {
            $file = is_null($suffix) ? runtime_path() . 'xdebug/' . date('Ymd') . '.txt' : runtime_path() . 'xdebug/' . date('Ymd') . "_{$suffix}" . '.txt';
        }
        file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . "========================= {$type} ===========================" . PHP_EOL, FILE_APPEND);
        $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
        $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
    }
}

if (!function_exists('sysconfig')) {

    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return array|mixed
     */
    function sysconfig($group, $name = null)
    {
        $where = ['group' => $group];
        $value = empty($name) ? Cache::get("sysconfig_{$group}") : Cache::get("sysconfig_{$group}_{$name}");
        if (empty($value)) {
            if (!empty($name)) {
                $where['name'] = $name;
                $value = \app\admin\model\SystemConfig::where($where)->value('value');
                Cache::tag('sysconfig')->set("sysconfig_{$group}_{$name}", $value, 3600);
            } else {
                $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
                Cache::tag('sysconfig')->set("sysconfig_{$group}", $value, 3600);
            }
        }
        return $value;
    }
}

if (!function_exists('array_format_key')) {

    /**
     * 二位数组重新组合数据
     * @param $array
     * @param $key
     * @return array
     */
    function array_format_key($array, $key)
    {
        $newArray = [];
        foreach ($array as $vo) {
            $newArray[$vo[$key]] = $vo;
        }
        return $newArray;
    }

}

if (!function_exists('auth')) {

    /**
     * auth权限验证
     * @param $node
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function auth($node = null)
    {
        $authService = new AuthService(session('admin.id'));
        $check = $authService->checkNode($node);
        return $check;
    }

}

//    post
 function HttpPost($content = null,$url='') {
    $postUrl = $url;
    $curlPost = $content;
    $ch = curl_init (); // 初始化curl
    curl_setopt ( $ch, CURLOPT_URL, $postUrl ); // 抓取指定网页
    curl_setopt ( $ch, CURLOPT_HEADER, 0 ); // 设置header
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 要求结果为字符串且输出到屏幕上
    curl_setopt ( $ch, CURLOPT_POST, 1 ); // post提交方式
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $curlPost );
    $data = curl_exec ( $ch ); // 运行curl
    curl_close ( $ch );
    return $data;
}
function http_post_data($url, $data_string) {
    $data_string = json_encode($data_string);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;

    curl_setopt($ch, CURLOPT_URL, $url);

    if($ssl){

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在

    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string))
    );

    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/

    //curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/


    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    $return_content = json_decode($return_content,true);
    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return array($return_code, $return_content);
}
//get
 function httpget($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko)");
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");//加入gzip解析
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}




//获取用户的所有下级ID
function get_downline($members,$mid,$level=0){
    $arr=array();
    foreach ($members as $key => $v) {
        if($v['higher_level_id']==$mid){  //pid为0的是顶级分类
            $v['level'] = $level+1;
            $arr[]=$v;
            $arr = array_merge($arr,get_downline($members,$v['id'],$level+1));
        }
    }
    return $arr;
}


function GetTeamMember($members,$mid) {
    $Teams=array();//最终结果
    $mids=array($mid);//第一次执行时候的用户id
    do {
        $othermids=array();
        $state=false;
        foreach ($mids as $valueone) {
            foreach ($members as $key =>$valuetwo) {
                if($valuetwo['higher_level_id']==$valueone){
                    $Teams[]=$valuetwo['id'];//找到我的下级立即添加到最终结果中
                    $othermids[]=$valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
                    array_splice($members,$key,1);//从所有会员中删除他
                    $state=true;
                }
            }
        }
        $mids=$othermids;//foreach中找到的我的下级集合,用来下次循环
    }while ($state==true);

    return $Teams;
}

function base64EncodeImage ($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

function send_sms ($AccessKeyId, $Secret, $params) {
    require '../vendor/autoload.php';
    if (empty($params['phone'])) {
        return false;
    }
    // 创建客户端
    AlibabaCloud\Client\AlibabaCloud ::accessKeyClient($AccessKeyId, $Secret)
        ->regionId('cn-hangzhou')
        ->asDefaultClient();
    try {
        $result =    AlibabaCloud\Client\AlibabaCloud::rpc()
            ->product('Dysmsapi')
            ->version('2017-05-25')
            ->action('SendSms')
            ->host('dysmsapi.aliyuncs.com')
            ->options([
                // 这里的参数可以在openAPI Explorer里面查看
                'query' => [
                    'RigionId'     => 'cn_hangzhou',
                    'PhoneNumbers' => $params['phone'],	// 输入的手机号
                    'SignName'     => $params['sign'],	// 签名信息
                    'TemplateCode' => $params['code'],	// 短信模板id
                    'TemplateParam' => $params['param']	// 可选，模板变量值，json格式
                ]
            ])
            ->request();
//        print_r($result->toArray());
        return $result->toArray();
    } catch (ClientException $e) {
        echo $e->getErrorMessage() . PHP_EOL;
    } catch (ServerException $e) {
        echo $e->getErrorMessage() . PHP_EOL;
    }
}
/**
 * 校验手机号码
 * @param $phone
 * @return bool
 */
function validatePhone ($phone) {
    if(!preg_match("/^1[34578]\d{9}$/", $phone)){
        return false;
    }
    return true;
}

////获取一个用户的最顶级,在模板控制器
// function merchants_sub($higher_level_id, $array = [])
//{
//    $merchants_users = \app\admin\model\SystemAdmin::where('id', $higher_level_id)->find()->toArray();
////        dump($merchants_users);die;
//    $array[] = $merchants_users;
//    if ($merchants_users['higher_level_id']) {
//        return self::merchants_sub($merchants_users['higher_level_id'], $array);
//    }
//
//    return $array;
//
//}


function tudincode($url = "http://www.baidu.com")
{
    require'../app/admin/help/phpqrcode/phpqrcode.php';
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
    return $imageString;

}


/**
 * 获取最近七天所有日期
 */
function get_weeks($time = '', $format='Y-m-d'){
    $time = $time != '' ? $time : time();
    //组合数据
    $date = [];
    for ($i=1; $i<=7; $i++){
        $date[$i] = date($format ,strtotime( '+' . $i-7 .' days', $time));
    }
    return $date;
}
//本月
function monthlater(){
    $str = array();
    for($i=0;$i<6;$i++){
        $str[$i] =date('Y-m',strtotime('-'.$i.'month'));//包含本月
        //$str[$i] =date('Y-m',strtotime('-1month-'.$i.'month'));//不包含本月
    }
    return $str;
}
