<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/15
 * Time: 18:23
 */

namespace app\admin\help;


class Apirequest
{

//    protected $api = 'https://pmpos.chanpay.com';//畅杰
    protected $api = 'https://acq-pos.furongyun.cn';//块钱

    protected $agentId;

    protected $AESKey;


    public function __construct($agentId, $AESKey,$api='https://acq-pos.furongyun.cn')
    {

        $this->agentId = $agentId;
        $this->$api = $agentId;
        $this->AESKey = $AESKey;

    }
    //查询商户费率
    public function getMerchantRate($merch_id)
    {
        $postUrl = $this->api . '/api/acq-channel-gateway/v1/acq-channel-service/getMerchantFeeInfo';
        $merchId = $merch_id;
        $token = $this->getToken('2062');
        $data['agentId'] = $this->agentId;
        $data['token'] = $token[1]['data']['token'];
        $data['merchId'] = $merchId;
        $data['sign'] = $this->signByMap($data);
        $result = http_post_data($postUrl, $data);

        return $result;
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

    //冻结服务费
    public function freeze($merch_id,$sn,$posCharge,$smsCode)
    {
        $postUrl = $this->api . '/api/acq-channel-gateway/v1/terminal-service/terms/activityReformV3/amountFrozen';
        $merchId = $merch_id;
        $token = $this->getToken('2083');
        $data['agentId'] = "$this->agentId";
        $data['traceNo'] =md5($merch_id);//请求流水号
        $data['merchId'] = $merchId;
        $data['directAgentId'] ="$this->agentId";
        $data['sn'] =$sn;
        $data['posCharge'] =$posCharge;
        $data['vipCharge'] =0;
        $data['simCharge'] =0;
        $data['smsSend'] ="1";
        $data['smsCode'] =$smsCode;
        $data['token'] = $token[1]['data']['token'];
        $data['sign'] = $this->signByMap($data);
        $result = http_post_data($postUrl, $data);
        return $result;
    }


    //冻结sim流量费
    public function simfreeze($merch_id,$sn,$simCharge,$smsCode)
    {
        $postUrl = $this->api . '/api/acq-channel-gateway/v1/terminal-service/terms/activityReformV3/amountFrozen';
        $merchId = $merch_id;
        $token = $this->getToken('2083');
        $data['agentId'] = "$this->agentId";
        $data['traceNo'] =md5($merch_id.'sim');//请求流水号
        $data['merchId'] = $merchId;
        $data['directAgentId'] ="$this->agentId";
        $data['sn'] =$sn;
        $data['posCharge'] =0;
        $data['vipCharge'] =0;
        $data['simCharge'] =$simCharge;
        $data['smsSend'] ="1";
        $data['smsCode'] =$smsCode;
        $data['token'] = $token[1]['data']['token'];
        $data['sign'] = $this->signByMap($data);
        $result = http_post_data($postUrl, $data);
        return $result;
    }



    //查询服务费冻结
    public function chaxun($merch_id,$optNo)
    {
        $postUrl = $this->api . '/api/acq-channel-gateway/v1/terminal-service/terms/activityReformV3/queryAmtInfo';
        $merchId = $merch_id;
        $data['agentId'] = "$this->agentId";
        $token = $this->getToken('2087');
        $data['token'] = $token[1]['data']['token'];
        $data['traceNo'] =md5($merch_id);//请求流水号
        $data['optNo'] =$optNo;//操作序列号
        $data['sign'] = $this->signByMap($data);
        $result = http_post_data($postUrl, $data);
        return $result;
    }
    //sim冻结查询
    public function simchaxun($merch_id,$optNo)
    {
        $postUrl = $this->api . '/api/acq-channel-gateway/v1/terminal-service/terms/activityReformV3/queryAmtInfo';
        $merchId = $merch_id;
        $data['agentId'] = "$this->agentId";
        $token = $this->getToken('2087');
        $data['token'] = $token[1]['data']['token'];
        $data['traceNo'] =md5($merch_id.'sim');//请求流水号
        $data['optNo'] =$optNo;//操作序列号
        $data['sign'] = $this->signByMap($data);
        $result = http_post_data($postUrl, $data);
        return $result;
    }


    //修改费率
    public function setMerchantRate($merch_id, $rate){
        $postUrl = $this->api.'/api/acq-channel-gateway/v1/acq-channel-service/merchant/fee/updateNonAudit';
        $merchId = $merch_id;
        $token = $this->getToken('2061');

        $data['agentId'] = $this->agentId;
        $data['token'] = $token[1]['data']['token'];
        $data['merchId'] = $merchId;

        $data['cFeeRate'] = $rate['cFeeRate'];
        $data['dFeeRate'] = $rate['dFeeRate'];
        $data['dFeeMax'] = $rate['dFeeMax'];
        $data['wechatPayFeeRate'] = $rate['wechatPayFeeRate'];
        $data['alipayFeeRate'] = $rate['alipayFeeRate'];
        $data['ycFreeFeeRate'] = $rate['ycFreeFeeRate'];
        $data['ydFreeFeeRate'] = $rate['ydFreeFeeRate'];
        //$data['d0FeeRate'] = 0;
        //$data['d0SingleCashDrawal'] = 100;
        $data['sign'] = $this->signByMap($data);
        $res = http_post_data($postUrl,$data);
        return $res;
    }


}
