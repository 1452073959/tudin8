<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/26
 * Time: 10:15
 */

namespace app\index\controller;

use app\admin\help\Apirequest;
use app\admin\model\deal\Deallist;
use app\admin\model\deal\Profit;
use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;
use app\admin\model\terminal\Freeze;
use app\BaseController;

class Task extends BaseController
{

    public function task()
    {
        //定时更新未激活和达标未激活
        $terminal = Channel::with(['jiju', 'tixian'])->select();
        foreach ($terminal as $k => $v) {
            $total = Deallist::where('terminal_id', $v['id'])->where('deal_status', 1)->sum('deal_money');
            if (time() < $v['activation_time'] + 86400 * $v['jiju']['activity_time'] && $total < $v['jiju']['activity_condition']) {
                $v['status'] = 6;//超期未激活:交易金额小于激活金额且已经超时过设定时间
            }
            if (time() < $v['reach_time'] + 86400 * $v['jiju']['reach_time'] && $total < $v['jiju']['reach_condition']) {
                $v['status'] = 5;//超期未达标:交易金额小于激活金额且已经超时过设定时间
            }
            //伪激活计算
            if ($v['disguiser'] == 0) {
                if ($v['reach_time']) {
//                第一个月
                    $time1 = $v['reach_time'];
                    $time2 = $v['reach_time'] + 2626560;
                    //第二个月结束
                    $time3 = $v['reach_time'] + 2626560 * 2;
                    //第三个月结束
                    $time4 = $v['reach_time'] + 2626560 * 3;
                    //第一个月交易金额
                    $mon1 = Deallist::whereBetweenTime('deal_create_time', $time1, $time2)->where('deal_status', 1)->where('terminal_id', $v['id'])->sum('deal_money');
                    //第二个月
                    $mon2 = Deallist::whereBetweenTime('deal_create_time', $time2, $time3)->where('deal_status', 1)->where('terminal_id', $v['id'])->sum('deal_money');
                    //第三个月
                    $mon3 = Deallist::whereBetweenTime('deal_create_time', $time3, $time3)->where('deal_status', 1)->where('terminal_id', $v['id'])->sum('deal_money');
                    if (time() > $time1) {
                        if ($mon1 < $v['jiju']['activity_false']) {
                            $v->disguiser = 1;
                            $v->save();
                            $profit = [
                                'agent_id' => $v['dls_id'],//代理商id
                                'terminal_id' => $v['id'],//机具id
                                'type' => 1,//机具id
                                'amount' => 0,//交易金额
                                'profit' => '-' . $v['jiju']['activity_false_withhold'],//分润
                                'tranTime' => time(),//交易时间
                                'tranCode' => 0,//交易码
                                'field' => '',//交易费率字段
                                'describe' => '第一个月伪激活不达标扣款',//交易描述
                                'createtime' => time(),//创建时间
                            ];
                            Profit::create($profit);
                        }
                    }

                    //第二个月开始
                    if (time() > $time2) {
                        if ($mon2 < $v['jiju']['activity_false']) {
                            $v->disguiser = 1;
                            $v->save();
                            $profit = [
                                'agent_id' => $v['dls_id'],//代理商id
                                'terminal_id' => $v['id'],//机具id
                                'type' => 1,//机具id
                                'amount' => 0,//交易金额
                                'profit' => '-' . $v['jiju']['activity_false_withhold'],//分润
                                'tranTime' => time(),//交易时间
                                'tranCode' => 0,//交易码
                                'field' => '',//交易费率字段
                                'describe' => '第二个月伪激活不达标扣款',//交易描述
                                'createtime' => time(),//创建时间
                            ];
                            Profit::create($profit);
                        }
                    }
                    //第三个月开始
                    if (time() > $time3) {
                        if ($mon3 < $v['jiju']['activity_false']) {
                            $v->disguiser = 1;
                            $v->save();
                            $profit = [
                                'agent_id' => $v['dls_id'],//代理商id
                                'terminal_id' => $v['id'],//机具id
                                'type' => 1,//机具id
                                'amount' => 0,//交易金额
                                'profit' => '-' . $v['jiju']['activity_false_withhold'],//分润
                                'tranTime' => time(),//交易时间
                                'tranCode' => 0,//交易码
                                'field' => '',//交易费率字段
                                'describe' => '第三个月伪激活不达标扣款',//交易描述
                                'createtime' => time(),//创建时间
                            ];
                            Profit::create($profit);
                        }
                    }
                }
            }

            //更新sim扣款,产生第一次交易发起冻结设定天书后发起冻结sim废,这里要用一级代理商
            $agent = SystemAdmin::where('appid', $v['top_code'])->find();
            $freeze = new Apirequest($agent['appid'], $agent['secret_key']);
                //参与活动
            if ($v['activity_sim'] == 2) {
                    //未成功收取和已激活
                if ($v['activity_freeze_status_sim'] != 2 && $v['merchant_code'] != '') {
                        //已发送请求和已产生交易
                    if ($v['send_sim'] == 1&&$v['reach_time']!='') {
                        //第一次交易后,多少天开始扣除流量费
                        if ($v['reach_time'] + 86400 * $v['sim_day'] < time()) {
                            $liu = $freeze->simfreeze($v['merchant_code'], $v['sn'], $v['sim_service_charge'], $v['sim_note_template']);
                            $log=new Freeze();
                            $log->sn=$v->sn;
                            $log->merchant_code=$v->merchant_code;
                            $log->merchant_name=$v->merchant_title;
                            $log->type=2;//流量费
                            $log->create_time=time();
                            $log->time=date('Y-m-d H:i:s');
                            if ($liu[1]['code'] == 00) {
                                $v->optNo_sim = $liu[1]['data']['optNo'];
                                $v->send_sim = 2;
                                $v->save();
                                $log->status=2;
                                $log->optNo=$liu[1]['data']['optNo'];
                            } else {
                                $v->optNo_sim = $liu['1']['message'];
                                $v->save();
                                $log->optNo=$liu['1']['message'];
                                $log->status=1;
                            }
                            $log->save();
                        }

                    }
                    //更新流量缴费状态
                    $ll = $freeze->simchaxun($v['merchant_code'], $v['optNo_sim']);
                    if ($ll[1]['code'] == 00 && $ll[1]['data']['merchPayResult'] == 1) {
                        $v->activity_freeze_status_sim = 2;
                        $v->save();
                    } else {
                        $v->activity_freeze_status_sim = 1;
                        $v->save();
                    }
                }
            }
            //参与服务费活动
            if ($v['activity'] == 2) {
                if ($v['activity_freeze_status'] != 2) {
                    //服务费缴费状态
                    $fw = $freeze->chaxun($v['merchant_code'], $v['optNo']);
                    dump($fw);
                    if ($fw[1]['code'] == 00 && $fw[1]['data']['merchPayResult'] == 1) {
                        $v->activity_freeze_status = 2;
                        $v->save();
                    } else {
                        $v->activity_freeze_status = 1;
                        $v->save();
                    }
                }
            }
        }
    }


}