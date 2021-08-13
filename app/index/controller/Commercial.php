<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/20
 * Time: 11:16
 */

namespace app\index\controller;


use app\admin\help\Apirequest;
use app\admin\help\Result;
use app\admin\model\deal\Deallist;
use app\admin\model\deal\Profit;
use app\admin\model\merchant\Merchantlist;
use app\admin\model\SystemAdmin;
use app\admin\model\template\Jiesuan;
use app\admin\model\template\Jiju;
use app\admin\model\template\Tixian;
use app\admin\model\terminal\Channel;
use app\admin\model\terminal\Terminancate;
use app\ApiController;
use app\Request;
use think\db\Query;
use  think\facade\Db;

class Commercial extends ApiController
{

    protected $middleware = ['\app\middleware\Check::class'];
    protected $user;

    //商户列表
    public function list(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $where = [];
        if (isset($req['search'])) {
            $where = [
                ['corporate_name', 'like', '%' . $req['search'] . '%']
            ];
        }
        //直属商户
        $data = Channel::with(['dls' => function (Query $query) {
            $query->field('id,name,name');
        }, 'cate'])->where('dls_id', $user['id'])->where($where)->where('merchant_code', '<>', '')->select();
        foreach ($data as $k => $v) {
            $data[$k]['all_profit'] = Profit::where('terminal_id', $v['id'])->sum('profit');
            $data[$k]['month_profit'] = Profit::where('terminal_id', $v['id'])->whereTime('createtime', 'month')->sum('profit');
            $data[$k]['month_deal'] = Deallist::where('terminal_id', $v['id'])->whereTime('deal_create_time', 'month')->sum('deal_money');
            if ($data[$k]['return_activate_time'] != null) {
                $data[$k]['return_activate_time'] = date('Y-m-d', $v['return_activate_time']);
            }

        }
        //代理商户
        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        $agent2 = GetTeamMember($agent1, $user['id']);
//        dump($agent2);die;
        $data1 = Channel::with(['dls' => function ($query) {
            $query->field('id,name,name');
        }, 'cate'])->wherein('dls_id', $agent2)->where($where)->where('merchant_code', '<>', '')->select();
        foreach ($data1 as $k => $v) {
            $data1[$k]['all_profit'] = Profit::where('terminal_id', $v['id'])->sum('profit');
            $data1[$k]['month_profit'] = Profit::where('terminal_id', $v['id'])->whereTime('createtime', 'month')->sum('profit');
            $data1[$k]['month_deal'] = Deallist::where('terminal_id', $v['id'])->whereTime('deal_create_time', 'month')->sum('deal_money');
            if ($v['return_activate_time'] != null) {
                $data1[$k]['return_activate_time'] = date('Y-m-d', $v['return_activate_time']);
            }
        }


        $all = ['zssh' => $data, 'dlsh' => $data1];

        return Result::Success($all);

    }

    //修改商户费率
    public function setmerchantRate(Request $request)
    {
        $user = $this->user($request);
        $data = $request->param();
        //验证
        $validate = new \think\Validate();
        $validate->rule([
            'merchant_code' => 'require',
            'terminal_id' => 'require',
            'cFeeRate' => 'require',
            'dFeeRate' => 'require',
            'dFeeMax' => 'require',
            'wechatPayFeeRate' => 'require',
            'alipayFeeRate' => 'require',
            'ycFreeFeeRate' => 'require',
            'ydFreeFeeRate' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Result::Error('1000', $validate->getError());
        }
        $setrate = [
            'cFeeRate' => $data['cFeeRate'],
            'dFeeRate' => $data['dFeeRate'],
            'dFeeMax' => $data['dFeeMax'],
            'wechatPayFeeRate' => $data['wechatPayFeeRate'],
            'alipayFeeRate' => $data['alipayFeeRate'],
            'ycFreeFeeRate' => $data['ycFreeFeeRate'],
            'ydFreeFeeRate' => $data['ydFreeFeeRate'],
        ];
        $res = Channel::where('id', $data['terminal_id'])->where('merchant_code', $data['merchant_code'])->find();
        if (!$res) {
            return Result::Error('1000', '商户号与SN不匹配');
        }
        //获取改商户一代代理商id
        $user = SystemAdmin::where('appid', $res['top_code'])->field('id,appid,secret_key')->find();

        if (!$user) {
            return Result::Error('1000', '机构号异常');
        }

        $rate = new Apirequest($user['appid'], $user['secret_key']);
        $rate1 = $rate->setMerchantRate($data['merchant_code'], $setrate);

        $merchantRate = $rate->getMerchantRate($data['merchant_code']);
        $res = Channel::update($merchantRate[1]['data'], ['id' => $data['terminal_id']]);
//        dump($res->toArray());die;
        return Result::Success($rate1[1]['message']);

    }

    //商户划拨
    public function merchanttransfer(Request $request)
    {
        $user = $this->user($request);
        $data = $request->param();
        //验证
        $validate = new \think\Validate();
        $validate->rule([
            'terminal_id' => 'require',
            'dls_id' => 'require',
            'jiesuan_id' => 'require',
            'jiju_id' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Result::Error('1000', $validate->getError());
        }
        $terminal = \app\admin\model\terminal\Channel::find($data['terminal_id']);
        //创建当级关系
        $td_channel_terminal = Db::name('channel_terminal')->insert(['channel_id' => $data['terminal_id'], 'sydls_id' => $data['dls_id'], 'jiesuan_id' => $data['jiesuan_id'],
            'form_id' => $user['id'],
//                    'to_id'=>$post1['dls_id'],
            'jiju_id' => $data['jiju_id'],
//            'tixian_id' => $data['tixian_id']
        ]);
        //更新上级代理商关系
        Db::name('channel_terminal')->where(['channel_id' => $data['terminal_id'], 'sydls_id' => $user['id']])->update(['to_id' => $data['dls_id']]);

        $terminal->dls .= ',' . $data['dls_id'];
        $terminal->dls_id = $data['dls_id'];
        $terminal->jiesuan_id = $data['jiesuan_id'];
        $terminal->jiju_id = $data['jiju_id'];
//                $terminal->tixian_id=$post1['tixian_id'];
        $terminal->save();

        return Result::Success($terminal, '成功');
    }

    //政策
    public function template(Request $request)
    {
        $data = $request->param();
        $user = $this->user($request);
        if ($user['auth_ids'] == 7) {
            $jiesuan = Jiesuan::where('agent_id', $user['id'])->field('tname,id')->select();
            $jiju = Jiju::where('agent_id', $user['id'])->field('tname,id')->select();
            return Result::Success([
                'jiesuan' => $jiesuan, 'jiju' => $jiju
            ], '模板获取成功');
        } else {
            $jiesuan = Jiesuan::where('agent_id', $user['id'])->field('tname,id')->select();
            $terminal = \app\admin\model\terminal\Channel::find($data['terminal_id']);
            $jiju = Jiju::where('id', $terminal['jiju_id'])->field('tname,id')->find();
            return Result::Success([
                'jiesuan' => $jiesuan, 'jiju' => [$jiju]
            ], '模板获取成功');
        }


    }

    //商户划波直属商户
    public function merchant(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $where = [];
        if (isset($req['search'])) {
            $where = [
                ['corporate_name', 'like', '%' . $req['search'] . '%']
            ];
        }
        //直属商户
        $data = Channel::with(['dls' => function (Query $query) {
            $query->field('id,name');
        }])->where('dls_id', $user['id'])->where($where)->select();
        foreach ($data as $k => $v) {
            $data[$k]['all_profit'] = Profit::where('terminal_id', $v['id'])->sum('profit');
            $data[$k]['month_profit'] = Profit::where('terminal_id', $v['id'])->whereTime('createtime', 'month')->sum('profit');
            $data[$k]['month_deal'] = Deallist::where('terminal_id', $v['id'])->whereTime('deal_create_time', 'month')->sum('deal_money');
        }
        return Result::Success($data);
    }

    //商户划拨伙伴下级代理商
    public function agency(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $where = [];
        if (isset($req['search'])) {
            $where = [
                ['name', 'like', '%' . $req['search'] . '%']
            ];
        }

        //代理商户
        $dlsh = SystemAdmin::where('higher_level_id', $user['id'])->field('id,name,phone,name')->where($where)->select();
        return Result::Success($dlsh);
    }


}