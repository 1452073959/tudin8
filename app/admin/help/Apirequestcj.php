<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/30
 * Time: 9:39
 */

namespace app\admin\help;


class Apirequestcj
{

    protected $api = 'https://pmpos.chanpay.com';

    protected $agentId;

    protected $AESKey;


    public function __construct($agentId, $AESKey)
    {

        $this->agentId = $agentId;

        $this->AESKey = $AESKey;

    }

    function signByMap($data)
    {
        $sign = '';
        if ($data) {
            ksort($data);
            foreach ($data as $key => $value) {
                if (isset($value)) {
                    $sign .= $value;
                }
            }
        }
        if ($sign) {
            $sign = md5($this->AESKey . $sign);
        }

        return $sign;
    }

    //获取token
    function getToken($tokenType = '2085')
    {
        $postUrl = $this->api . '/api/acq-channel-gateway/v1/acq-channel-auth-service/tokens/token';
        $data['agentId'] = $this->agentId;
        $data['tokenType'] = $tokenType;
        $data['sign'] = $this->signByMap($data);
        $res = http_post_data($postUrl, $data);
        return $res;
    }
}