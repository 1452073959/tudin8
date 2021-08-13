<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/30
 * Time: 10:47
 */

namespace app\notice\controller;

use app\admin\help\Apirequest;
use app\admin\help\Apirequestcj;
use app\admin\model\deal\Deallist;
use app\admin\model\deal\Profit;
use app\admin\model\merchant\Merchantlist;
use app\admin\model\SystemAdmin;
use app\admin\model\template\Jiesuan;
use app\admin\model\terminal\Channel;
use app\BaseController;
use app\common\auth\JwtAuth;
use app\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;
use think\facade\Cache;
use think\facade\Db;

class Changjie extends BaseController
{
    public function index(Request $request)
    {
        $data = [
            'configAgentId' => "68003526", //机构号
            'dataList' => [
                [
                    'sysRespDesc' => '交易成功',//收单平台应答描述
                    'agentId' => '68003526',//商户直属机构号
//                    'amount' => '182500',//交易金额
                    'amount' => '375500',//交易金额
                    'batchNo' => '000007',//终端批次号②
                    'authCode' => '246430',//授权码
                    'sysRespCode' => '00',//收单平台应答码，详见：附件B
                    'traceNo' => '000105', //凭证号①
                    'settleAmount' => '373454',//结算金额
                    'cardType' => '1',//卡类型 0:借记卡，1:信用卡
                    'settleDate' => '20210810',//清算日期
                    'mobileNo' => '186****1808',//商户手机号
                    'feeType' => 'B',//手续费计算类型 Y - 优惠,M - 减免,B - 标准,YN - 云闪付NFC, YM - 云闪付双免
                    'cardNo' => '625249******1361',//卡号(带*)
                    'termModel' => 'H9',//终端型号
                    'merchLevel' => '3',//	商户类别 1-A类商户；2-B类商户；3-C类商户； 4-Z 类商户（机构自定义）
                    'merchantName' => '个体商户刘兵',//商户名称
                    'rrn' => '121125825810',//参考号
                    'sysTraceNo' => '000105',//系统流水号
                    'termId' => 'B0716de4',//终端号
                    'termSn' => '912104033610523',//终端SN
                    'tranTime' => '20210809121125',//交易时间 yyyyMMddhhmmss
                    'merchantId' => '150421594027686',//商户号
                    'inputMode' => '071',//输入方式
                    'tranCode' => '020000', //交易码
                ],
                [
                'sysRespDesc' => '交易成功',//收单平台应答描述
                'agentId' => '68003526',//商户直属机构号
//                    'amount' => '182500',//交易金额
                'amount' => '364000',//交易金额
                'batchNo' => '000007',//终端批次号②
                'authCode' => '000225',//授权码
                'sysRespCode' => '00',//收单平台应答码，详见：附件B
                'traceNo' => '000104', //凭证号①
                'settleAmount' => '362016',//结算金额
                'cardType' => '1',//卡类型 0:借记卡，1:信用卡
                'settleDate' => '20210810',//清算日期
                    'mobileNo' => '186****1808',//商户手机号
                'feeType' => 'B',//手续费计算类型 Y - 优惠,M - 减免,B - 标准,YN - 云闪付NFC, YM - 云闪付双免
                'cardNo' => '526855******8113',//卡号(带*)
                'termModel' => 'H9',//终端型号
                'merchLevel' => '3',//	商户类别 1-A类商户；2-B类商户；3-C类商户； 4-Z 类商户（机构自定义）
                'merchantName' => '个体商户刘兵',//商户名称
                'rrn' => '120920824237',//参考号
                'sysTraceNo' => '000104',//系统流水号
                'termId' => 'B0716de4',//终端号
                'termSn' => '912104033610523',//终端SN
                'tranTime' => '20210809120920',//交易时间 yyyyMMddhhmmss
                'merchantId' => '150421594027686',//商户号
                'inputMode' => '021',//输入方式
                'tranCode' => '020000', //交易码
            ]
            ],
            'dataType' => 1,
            'sendBatchNo' => '000117',// M 交易通知推送批次号
            'sendNum' => 1,//// M 数据推送的记录数
            'sendTime' => '20210728165319', // M 推送时间 yyyyMMddHHmmss
            'sign' => 'd8673a43e4ae242cf09f35ddd63e32ff',// M 签名（对请求的参数名进行按字母排序，将分配的3DES密钥（明文）和参数的值（明文）进行拼接后进行MD5加密得到签名结果）
            'transDate' => '20210728',// M 交易日期 yyyyMMdd
        ];

//        $data = input('post.');
        Cache::set('changjie', $data);

        if (empty($data)) {
            return '无数据传入';
        }


        if ($data['dataType'] == 0) {
            return $this->commercial($data);
        } else {
            return $this->payment($data);
        }

    }

    //商户开通通知
    protected function commercial($data)
    {
        //判断收到的通知是否是本系统代理商
        $agent = SystemAdmin::where('appid', $data['configAgentId'])->find();

        if ($agent) {
            foreach ($data['dataList'] as $k => $v) {
// 启动事务
                Db::startTrans();
                try {
                    //商户
                    $merch = [];
                    $merch['merchant_number'] = $v['merchantId'];//商户号
                    //终端
                    $terminal = [];
                    //机具
                    $machines = Channel::where('sn', $v['termSn'])->find();

                    if (!$machines) {
                        $response['configAgentId'] = $v['agentId'];
                        $response['dataType'] = "0";
                        $response['responseCode'] = "00";
                        $response['responseDesc'] = "机具不存在";
                        $response['sendBatchNo'] = $data['sendBatchNo'];
                        $response['transDate'] = $data['transDate'];
                        $response['sign'] = $data['sign'];
                        return json([$response]);
                    }
                    //                //费率
                    $rate = new Apirequest($agent['appid'], $agent['secret_key']);
                    $result = $rate->getMerchantRate($v['merchantId']);
                    if ($result[1]['code'] == 00) {
                        $rate = $result[1]['data'];
                        $merch = array_merge($rate, $merch);
                    }


                    Channel::update($merch, ['id' => $machines['id']]);
                    if ($machines['activity'] == 1) {
                        $machines->status = 7;
                        $machines->save();
                    }

                    if ($machines['activity'] == 2) {
                        $freeze = new Apirequest($agent['appid'], $agent['secret_key']);
                        //冻结费用,服务费
                        $freeze = $freeze->freeze($v['merchantId'], $v['termSn'], $machines['di_service_charge'], $machines['pos_note_template']);

                        if ($freeze[1]['code'] == 00) {
                            $machines->optNo = $freeze[1]['data']['optNo'];
                            $machines->save();
                        } else {
                            $machines->optNo = $freeze['1']['message'];
                            $machines->save();
                        }
                    }
                    $response['configAgentId'] = $v['agentId'];
                    $response['dataType'] = "0";
                    $response['responseCode'] = "00";
                    $response['responseDesc'] = "成功";
                    $response['sendBatchNo'] = $data['sendBatchNo'];
                    $response['transDate'] = $data['transDate'];
                    $response['sign'] = $data['sign'];
                    // 提交事务
                    Db::commit();
                    return json([$response]);
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    $response['configAgentId'] = $v['agentId'];
                    $response['dataType'] = "0";
                    $response['responseCode'] = "01";
                    $response['responseDesc'] = "失败";
                    $response['sendBatchNo'] = $data['sendBatchNo'];
                    $response['transDate'] = $data['transDate'];
                    $response['sign'] = $data['sign'];
                    return json([$response]);
                }
            }
        }
    }

    //收款通知
    protected function payment($data)
    {
        //判断收到的通知是否是本系统代理商
        $agent = SystemAdmin::where('appid', $data['configAgentId'])->find();
        if ($agent) {
            //添加流水
            foreach ($data['dataList'] as $k => $v) {
                if ($v['sysRespCode'] == '00') {
                    // 启动事务
                    Db::startTrans();
                    try {
                        //终端
                        $terminal = [];
                        //机具 2为畅捷
                        $machines = Channel::with(['jiju', 'tixian'])->where('sn', $v['termSn'])->find();
                        //如果没有分配代理商,代理商为一代
                        //机具存在
                        if ($machines) {
                            if ($machines['dls_id'] == null) {
                                $machines['dls_id'] = SystemAdmin::where('appid', $machines['top_code'])->value('id');

                            }
                            $terminal['merchant_code'] = $v['merchantId'];//商户号
                            $terminal['merchant_title'] = $v['merchantName'];//商户名称
//                            $terminal['reach_time'] = time();//第一笔交易产生时间
                            //历史本机交易额+本次交易额

                            $total = Deallist::where('terminal_id', $machines['id'])->where('deal_status', 1)->sum('deal_money') + $v['amount'] / 100;
                            //查询是否存在历史交易,没有则本笔为第一笔交易
                            $num = Deallist::where('terminal_id', $machines['id'])->where('deal_status', 1)->count();

                            if ($num <= 0) {
                                //第一笔交易.记录交易时间,
                                $terminal['reach_time'] = time();
                            }
                            //参与服务费活动,修改状态
                            if ($machines['activity'] == 2) {
                                if ($total < $machines['jiju']['activity_condition']) {
                                    $terminal['status'] = 1;//未激活:未达到激活金额,
                                }
                                if ($total >= $machines['jiju']['activity_condition']) {
                                    $terminal['status'] = 2;//已激活:达到激活金额,,
                                    $terminal['return_activate_time'] = time();//已激活:激活时间
                                    if ($machines['return_activate'] == 0) {
                                        $profit = [
                                            'agent_id' => $machines['dls_id'],//代理商id
                                            'terminal_id' => $machines['id'],//机具id
                                            'type' => 2,//机具id
                                            'amount' => 0,//交易金额
                                            'profit' => $machines['jiju']['activity_return'] * (1 - $machines['tixian']['jijufan_rate']),//分润
                                            'tranTime' => $v['tranTime'],//交易时间
                                            'tranCode' => $v['tranCode'],//交易码
                                            'field' => '',//交易费率字段
                                            'describe' => '达到激活金额返现',//交易描述
                                            'createtime' => time(),//创建时间
                                        ];
                                        Profit::create($profit);
                                        $terminal['return_activate'] = 1;//更新状态
                                        $res = SystemAdmin::find($machines['dls_id']);     //增加余额
                                        $res->return_balance = $res['return_balance'] + $profit['profit'];
                                        $res->save();
                                    }
                                }

                                if ($total >= $machines['jiju']['reach_condition']) {
                                    $terminal['status'] = 4;//已达标:达到达标金额
                                    if ($machines['retuen_reach'] == 0) {
                                        $profit = [
                                            'agent_id' => $machines['dls_id'],//代理商id
                                            'terminal_id' => $machines['id'],//机具id
                                            'amount' => 0,//交易金额
                                            'type' => 2,//机具id
                                            'profit' => $machines['jiju']['reach_return'] * (1 - $machines['tixian']['jijufan_rate']),//分润
                                            'tranTime' => $v['tranTime'],//交易时间
                                            'tranCode' => $v['tranCode'],//交易码
                                            'field' => '',//交易费率字段
                                            'describe' => '达到达标金额返现',//交易描述
                                            'createtime' => time(),//创建时间
                                        ];
                                        Profit::create($profit);
                                        $terminal['retuen_reach'] = 1;//更新状态
                                        $res = SystemAdmin::find($machines['dls_id']);     //增加余额
                                        $res->return_balance = $res['return_balance'] + $profit['profit'];
                                        $res->save();
                                    }

                                }
                                //更新机具相关信息
                                Channel::update($terminal, ['id' => $machines['id']]);
                            }


                            //商户
                            $merch = [];
                            $merch['merchant_title'] = $v['merchantName'];//商户名称
                            $merch['corporate_name'] = str_replace("个体商户", "", $v['merchantName']);//法人名称
                            $merch['merchant_code'] = $v['merchantId'];//商户号
                            $merch['tel'] = $v['mobileNo'];//商户手机号
                            $merch['deal_sum'] = Deallist::where('merchant_code', $v['merchantId'])->where('deal_status', 1)->sum('deal_money') + $v['amount'] / 100;//商户交易额
//                            $merch['dls_id'] = $machines['dls_id'];//代理商编号???

                            //流水
                            $deal = [];
                            $deal['deal_money'] = $v['amount'] / 100;            //交易金额
                            $deal['settleamount_money'] = $v['settleAmount'] / 100;//结算金额
                            $deal['service_money'] = $deal['deal_money'] - $deal['settleamount_money'];//手续费
                            $deal['deal_type'] = $v['cardType'];//交易类型
                            $deal['deal_time'] = $v['tranTime'];//交易完成时间
                            $deal['deal_number'] = $v['sysTraceNo'];//渠道交易号
                            $deal['dls_id'] = $machines['dls_id'];//代理商id??
                            $deal['deal_status'] = $v['sysRespCode'] == '00' ? '1' : '0';//交易状态
                            $deal['merchant_name'] = $v['merchantName'];//商户名称
                            $deal['merchant_code'] = $v['merchantId'];//商户编号
                            $deal['organization'] = $machines['brand'];//品牌
                            $deal['terminal_id'] = $machines['id'];//机具id
                            $deal['top_code_deal'] = $machines['top_code'];//一代id

                            //费率
                            $rate = new Apirequest($agent['appid'], $agent['secret_key']);
                            $result = $rate->getMerchantRate($v['merchantId']);
                            if ($result[1]['code'] == 00) {
                                $rate = $result[1]['data'];
                                $merch = array_merge($rate, $merch);
                            }
                            //更新商户信息
                            Channel::update($merch, ['id' => $machines['id']]);
                            //新增流水
                            $deal = Deallist::create($deal);
                            //获取此次交易所使用的费率        示例:                  [ "describe" => "POS借记卡刷卡消费", "ratetype" => "dFeeRate"];

                            $ratetype = $this->getTranCode($v['tranCode'], $v['cardType']);

                            //分润
                            //本机器所有代理商关系
                            $a = Channel::with(['js1'])->where('id', $machines['id'])->find();

                            foreach ($a['js1'] as $k2 => $v2) {
                                if (!$v2['pivot']['to_id']) {
                                    //如果交易使用借记卡费率
                                    if ($ratetype['ratetype'] == 'dFeeRate') {
                                        $temp = ($deal['deal_money'] * $a['dFeeRate'] / 100) > $a['dFeeMax'];
                                        if ($temp) {
                                            $ly = $a['dFeeMax'] - $v2['dFeeMax'];
                                        }
                                    }
                                    //  借记卡封顶值计算交易金额*商户费率>商户封顶值
                                    $ly = $deal['deal_money'] * (($a[$ratetype['ratetype']] - $v2[$ratetype['ratetype']]) / 100);

                                } else {
                                    //下级代理商关联关系/下级结算模板
                                    $to = Db::name('channel_terminal')->where('sydls_id', $v2['pivot']['to_id'])->where('channel_id', $machines['id'])->find();
                                    $tojiesuan = Jiesuan::where('id', $to['jiesuan_id'])->find();
                                    //正常费率
                                    $ly = $deal['deal_money'] * (($tojiesuan[$ratetype['ratetype']] - $v2[$ratetype['ratetype']]) / 100);
                                    if ($ratetype['ratetype'] == 'dFeeRate') {
                                        $temp = ($deal['deal_money'] * $a['dFeeRate'] / 100) > $a['dFeeMax'];
                                        if ($temp) {
                                            $ly = $a['dFeeMax'] - $v2['dFeeMax'];
                                        }
                                    }

                                }

                                // 保留两位不四舍五入
                                $ly = sprintf("%.2f", substr(sprintf("%.3f", $ly), 0, -1));
                                $profit = [
                                    'agent_id' => $v2['pivot']['sydls_id'],//代理商id
                                    'terminal_id' => $machines['id'],//机具id
                                    'amount' => $deal['deal_money'],//交易金额
                                    'profit' => $ly,//分润
                                    'deal_id' => $deal['id'],//流水
                                    'tranTime' => $v['tranTime'],//交易时间
                                    'tranCode' => $v['tranCode'],//交易码
                                    'field' => $ratetype['ratetype'],//交易费率字段
                                    'describe' => $ratetype['describe'],//交易费率
                                    'createtime' => time(),//创建时间
                                ];
                                Profit::create($profit);
                                //增加分润余额
                                $res = SystemAdmin::find([$v2['pivot']['sydls_id']]);
                                $res->profit_balance = $res['profit_balance'] + $ly;
                                $res->save();
                            }
                            $response['responseCode'] = '00';
                            // 提交事务
                            Db::commit();
                            $response['configAgentId'] = $data['configAgentId'];
                            $response['dataType'] = 1;
                            $response['responseDesc'] = '通知成功';
                            $response['revTime'] = $data['sendTime'];
                            $response['transDate'] = $data['transDate'];
                            $response['sendBatchNo'] = $data['sendBatchNo'];
                            $response['sign'] = $data['sign'];
                            return json($response);

                        } else {
                            return json('该sn不存在');
                        }

                    } catch (\Exception $e) {
                        return json($e->getMessage());
                    }
                }

            }

        } else {
            $response['responseCode'] = '00';
            $response['configAgentId'] = $data['configAgentId'];
            $response['dataType'] = 1;
            $response['responseDesc'] = '通知成功,该代理不存在';
            $response['revTime'] = $data['sendTime'];
            $response['transDate'] = $data['transDate'];
            $response['sendBatchNo'] = $data['sendBatchNo'];
            $response['sign'] = $data['sign'];
            return json($response);
        }
    }


    public function getTranCode($code = '020000', $cardType = '1')
    {
        switch ($code) {
            case '020000':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS信用卡刷卡消费', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS借记卡刷卡消费', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '020002':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡消费撤销', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡消费撤销', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '020003':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡消费冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡消费冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '020023':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡消费撤销冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡消费撤销冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case 'U20000':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡电子现金', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡电子现金', 'ratetype' => 'dFeeRate'];
                }
                break;
            case 'T20003':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡日结消费冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡日结消费冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case 'T20000':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡日结消费', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡日结消费', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024100':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024102':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成撤销', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成撤销', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024103':

                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024123':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成撤销 冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成撤销 冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '02B100':
                $field = ['describe' => '扫码交易支付宝被扫', 'ratetype' => 'alipayFeeRate'];
                break;
            case '02B200':
                $field = ['describe' => '扫码交易支付宝主扫', 'ratetype' => 'alipayFeeRate'];
                break;
            case '02W100':
                $field = ['describe' => '扫码交易微信被扫', 'ratetype' => 'wechatPayFeeRate'];
                break;
            case '02W200':
                $field = ['describe' => '扫码交易微信主扫', 'ratetype' => 'wechatPayFeeRate'];
                break;
            case '02Y100':
                $field = ['describe' => '扫码交易银联被扫', 'ratetype' => 'ycFreeFeeRate'];
                break;
            case '02Y200':
                $field = ['describe' => '扫码交易银联主扫', 'ratetype' => 'ycFreeFeeRate'];
                break;
            case '02Y600':
                $field = ['describe' => '扫码交易银联二维码撤销', 'ratetype' => 'ycFreeFeeRate'];
                break;
            case '0AY100':
                if ($cardType == 1) {
                    $field = ['describe' => 'APP交易APP银联被扫', 'ratetype' => 'ycFreeFeeRate '];
                } else {
                    $field = ['describe' => 'APP交易APP银联被扫', 'ratetype' => 'ydFreeFeeRate '];
                }
                break;
            case '0AY200':
                if ($cardType == 1) {
                    $field = ['describe' => 'APP交易APP银联主扫', 'ratetype' => 'ycFreeFeeRate '];
                } else {
                    $field = ['describe' => 'APP交易APP银联主扫', 'ratetype' => 'ydFreeFeeRate '];
                }
                break;
            default:
                $field = ['describe' => 'POS刷卡消费', 'ratetype' => 'cFeeRate'];
                break;
        }

        return $field;
    }

}