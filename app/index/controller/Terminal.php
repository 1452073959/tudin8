<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/21
 * Time: 16:41
 */

namespace app\index\controller;

use app\admin\help\Result;
use app\admin\model\SystemAdmin;
use app\admin\model\template\Jiesuan;
use app\admin\model\template\Jiju;
use app\admin\model\template\Tixian;
use app\admin\model\terminal\Channel;
use app\admin\model\terminal\Terminalreturn;
use app\admin\model\terminal\Terminancate;
use app\ApiController;
use app\Request;
use think\db\Query;
use  think\facade\Db;

class Terminal extends ApiController
{
    protected $middleware = ['\app\middleware\Check::class'];

//    我的机具
    public function cate(Request $request)
    {
        $user = $this->user($request);
//        $req = $request->param();
        $cate = Terminancate::select()->toArray();
//        echo 1;
//        $self= SystemAdmin::with(['terminal'=> function (Query $query) {
//            $query->where('cate_id',1);
//        }])->where('id',$user['id'])->find();
//       dump($self->toArray());die;

        foreach ($cate as $k => $v) {
            //自己已分配的数量
//            $cate[$k]['allocated']= SystemAdmin::withcount(['form'=> function (Query $query)use($v) {
//                $query->where('cate_id',$v['id']);
//            }])->where('id',$user['id'])->field('id,name')->find();
//            $cate[$k]['allocated']=Db::name('channel_terminal')->where('form_id',$user['id'])->where('sydls_id','<>',$user['id'])->count();
            $cate[$k]['allocated'] = Channel::where('dls', 'like', '%' . $user['id'] . '%')->where('cate_id', $v['id'])->where('dls_id', '<>', $user['id'])->count();
            //总库存
            $cate[$k]['current'] = Channel::where('cate_id', $v['id'])->whereLike('dls', "%" . $user['id'] . "%")->count();
            //未绑定的
            $cate[$k]['ybind'] = Channel::where('cate_id', $v['id'])->where('dls_id', $user['id'])->where('merchant_code', '<>', '')->count();
            $cate[$k]['now'] = $cate[$k]['current'] - $cate[$k]['allocated'] - $cate[$k]['ybind'];
        }

        return Result::Success($cate);

    }

    //机具划拨
    public function terminaltransfer(Request $request)
    {
        $user = $this->user($request);
        $data = $request->param();
//        $val = explode(",", $data['snid']);
        Db::startTrans();
        try {
            foreach ($data['snid'] as $k => $v) {
                $terminal = \app\admin\model\terminal\Channel::with(['jiesuan'])->find($v);
                $new = Jiesuan::find($data['jiesuan_id'])->toArray();
                if (
                    $terminal['jiesuan']['cFeeRate'] >= $new['cFeeRate'] ||
                    $terminal['jiesuan']['dFeeRate'] >= $new['dFeeRate'] ||
                    $terminal['jiesuan']['dFeeMax'] >= $new['dFeeMax'] ||
                    $terminal['jiesuan']['wechatPayFeeRate'] >= $new['wechatPayFeeRate'] ||
                    $terminal['jiesuan']['alipayFeeRate'] >= $new['alipayFeeRate'] ||
                    $terminal['jiesuan']['ycFreeFeeRate'] >= $new['ycFreeFeeRate'] ||
                    $terminal['jiesuan']['ydFreeFeeRate'] >= $new['ydFreeFeeRate']

                ) {
                    // 回滚事务
                    Db::rollback();
                    return Result::Error('1000', '选择模板的费率小于上级模板');
                }

                //更新上级代理商关系
                Db::name('channel_terminal')->where(['channel_id' => $terminal['id'], 'sydls_id' => $user['id']])->update(['to_id' => $data['dls_id']]);

                $terminal->dls .= ',' . $data['dls_id'];
                $terminal->dls_id = $data['dls_id'];
                $terminal->jiesuan_id = $data['jiesuan_id'];
                if ($user['auth_ids'] == 7) {
                    $terminal->activation_time = time();
                    $terminal->jiju_id = $data['jiju_id'];
                    $terminal->tixian_id = $data['tixian_id'];
                    //中间表添加数据
                    $td_channel_terminal = Db::name('channel_terminal')->insert(['channel_id' => $terminal['id'], 'sydls_id' => $data['dls_id'], 'jiesuan_id' => $data['jiesuan_id'],
                        'form_id' => $user['id'],
                        'jiju_id' => $data['jiju_id'], 'tixian_id' => $data['tixian_id']]);
                } else {
                    $td_channel_terminal = Db::name('channel_terminal')->insert(['channel_id' => $terminal['id'], 'sydls_id' => $data['dls_id'], 'jiesuan_id' => $data['jiesuan_id'],
                        'form_id' => $user['id'],
                        'jiju_id' => $terminal['jiju_id'], 'tixian_id' => $terminal['tixian_id']]);
                }
                $terminal->save();
            }
            Db::name('terminal_action')->insert([
                'sn' => implode(',', $data['snid']), 'dls' => $data['dls_id'], 'action' => 'app划拨', 'cate_id' => $terminal['cate_id'], 'create_time' => date('Y-m-d H:i', time()), 'agent_id' => $user['id']
            ]);
            // 提交事务
            Db::commit();
            return Result::Success('划拨成功');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return Result::Error('1000', '失败');
        }


    }

    //本人未激活的
    public function noallocated(Request $request)
    {
        $user = $this->user($request);
        $data = $request->param();
        $noallocated = Channel::where('cate_id', $data['cate_id'])->where('dls_id', $user['id'])->where('merchant_code', '')->where('jiesuan_id',$data['jiesuan_id'])->order('sn', 'asc')->select();
        return Result::Success($noallocated);
    }




    //代理商政策详情
    public function agentdetails(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //获取改代理商所有机具政策关联信息
        $a = Db::name('channel_terminal')->where('sydls_id', $user['id'])->select()->toArray();
        //结算政策
        $jeisuan = array_column($a, 'jiesuan_id');
        $jeisuan = array_unique($jeisuan);
        $jiesuan = Jiesuan::where('id', 'in', $jeisuan)->select();
        return Result::Success([
            'jiesuan' => $jiesuan,
        ]);
    }
    //模板
    public function template(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        if ($user['auth_ids'] == 7) {
            $jiesuan = Jiesuan::where('agent_id', $user['id'])->field('tname,id')->select();
            $jiju = Jiju::where('agent_id', $user['id'])->field('tname,id')->select();
            $tixian = Tixian::where('agent_id', $user['id'])->field('tname,id')->select();
            return Result::Success([
                'jiesuan' => $jiesuan, 'jiju' => $jiju, 'tixian' => $tixian
            ], '模板获取成功');
        } else {
            $jiesuan = Jiesuan::where('agent_id', $user['id'])->field('tname,id')->select();
            return Result::Success([
                'jiesuan' => $jiesuan,
            ], '模板获取成功');
        }

    }

    //划拨记录
    public function transfer(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        $res = Db::name('terminal_action')->where('agent_id', $user['id'])->where('cate_id', $data['cate_id'])->order('create_time', 'desc')->select()->toArray();
        if ($res) {
            foreach ($res as $k => $v) {
                $res[$k]['agent'] = SystemAdmin::where('id', $v['dls'])->find();
                $res[$k]['allsn'] = array_column(Channel::where('id', 'in', $v['sn'])->field('id,sn')->select()->toArray(), 'sn');
            }
            return Result::Success($res);
        } else {
            return Result::Error('1000', '暂无分配记录');
        }
    }

    //机具回拨
    public function terminalreturn(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        $retuns = new Terminalreturn();
        $retuns->form_id = $user['id'];
        $retuns->to_id = $data['to_id'];
        $retuns->sn = implode(',', $data['sn']);
        $retuns->status = 1;
        $retuns->save();
        if ($retuns) {
            return Result::Success($retuns, '申请成功');
        } else {
            return Result::Success($retuns, '失败');
        }

    }

    //机具申请回拨记录(回拨人)
    public function application_list(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        $list = Terminalreturn::with(['form' => function (Query $query) {
            $query->field('phone,name,id');
        }, 'to' => function (Query $query) {
            $query->field('phone,name,id');
        }])->where('form_id', $user['id'])->order('status', 'asc')->select()->toArray();;

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['allsn'] = Channel::with('cate')->where('id', 'in', $v['sn'])->field('id,sn,cate_id')->select()->toArray();
                $list[$k]['yes'] =1;
            }
            return Result::Success($list);
        } else {
            return Result::Success($list,'暂无记录');
        }

    }


    //机具申请回拨记录(接受人)
    public function application_list_accepter(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        $list = Terminalreturn::with(['form' => function (Query $query) {
            $query->field('phone,name,id');
        }, 'to' => function (Query $query) {
            $query->field('phone,name,id');
        }])->where('to_id', $user['id'])->order('status', 'asc')->select()->toArray();

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['allsn'] = Channel::with('cate')->where('id', 'in', $v['sn'])->field('id,sn,cate_id')->select()->toArray();
            }
            return Result::Success($list);
        } else {
            return Result::Success($list,'暂无记录');

        }
    }

    //机具回拨同意
    public function audit(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        // 启动事务
        Db::startTrans();
        try {
            $neq = Terminalreturn::where('id', $data['id'])->find();
            //获取上级的模板信息
            //更新机具表
            $terminal = Channel::where('id', 'in', $neq['sn'])->select();
            foreach ($terminal as $k => $v) {
                $v->dls_id = $neq['to_id'];
                $a = explode(',', $v['dls']);
                array_pop($a);

                $up = Db::name('channel_terminal')->where('sydls_id', $neq['to_id'])
                    ->where('to_id', $neq['form_id'])
                    ->where('channel_id', '=', $v['id'])
                    ->find();

                $v->dls = implode($a, ',');
                $v->jiesuan_id = $up['jiesuan_id'];
                $v->jiju_id = $up['jiju_id'];
                $v->tixian_id = $up['jiesuan_id'];
                $v->save();
            }

            //机器是自己的//删除自己的记录
            $res = Db::name('channel_terminal')->where('sydls_id', $neq['form_id'])
                ->where('form_id', $neq['to_id'])
                ->where('to_id', null)
                ->where('channel_id', 'in', $neq['sn'])
                ->delete();
            //更新上级的记录
            Db::name('channel_terminal')->where('sydls_id', $neq['to_id'])
                ->where('to_id', $neq['form_id'])
                ->where('channel_id', 'in', $neq['sn'])
                ->update(['to_id' => null]);
            //回拨记录
            Db::name('terminal_action')->insert([
                'sn' => $neq['sn'], 'dls' => $neq['to_id'], 'action' => 'app回拨', 'cate_id' => $v['cate_id'], 'create_time' => date('Y-m-d H:i', time()), 'agent_id' => $neq['form_id']
            ]);
            $neq->status = 3;
            $neq->time = time();
            $neq->save();
            // 提交事务
            Db::commit();
            return Result::Success('审批成功');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return Result::Error('1000', $e->getMessage());
        }

    }

    //审批拒绝
    public function audit_no(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        $neq = Terminalreturn::where('id', $data['id'])->find();
        $neq->status = 2;
        $neq->save();
        if ($neq) {
            return Result::Success('拒绝成功');
        } else {
            return Result::Error('1000', '失败');
        }
    }

    //获取用户上级
    public function superior_agent(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        $agent = SystemAdmin::where('id', $user['higher_level_id'])->select();
        return Result::Success($agent);
    }

    //获取未绑定商户的机器
    public function nobind(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        //自己名下,未激活的机器
        $terminal = Channel::where('dls_id', $user['id'])->where('merchant_code', '=', '')->where('cate_id', $data['cate_id'])->field('id,sn')->select();
        return Result::Success($terminal);
    }


}