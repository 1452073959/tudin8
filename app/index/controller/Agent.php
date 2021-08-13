<?php

namespace app\index\controller;

use app\admin\model\deal\Profit;
use app\admin\model\template\Jiesuan;
use app\admin\model\template\Jiju;
use app\admin\model\template\Tixian;
use app\admin\model\terminal\Channel;
use app\admin\help\Result;
use app\admin\model\deal\Deallist;
use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Terminancate;
use app\ApiController;
use app\Request;
use think\db\Query;
use think\facade\Db;

class Agent extends ApiController
{
    protected $middleware = ['\app\middleware\Check::class'];

    //代理商管理
    public function agent(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //下级代理商数量

        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        //所有下级
//        $agent2 = get_downline($agent1, $user['id']);
        //直属
        $where = [];
        if (isset($req['search'])) {
            $where = [
                ['name', 'like', '%' . $req['search'] . '%']
            ];
        }
        $agent2 = SystemAdmin::field('id,name,name,phone,higher_level_id')->where('higher_level_id', $user['id'])->where($where)->select()->toArray();

        //所有下级代理
        $col = [];
        foreach ($agent2 as $k1 => $v1) {
            $col[] = $v1;
            //直属分润

//            直属下级机器
            $zh = Db::name('channel_terminal')->where('sydls_id', $v1['id'])->select()->toArray();
            $zh = array_unique(array_column($zh, 'channel_id'));
            //           所有下级的机器
            $cs = Db::name('channel_terminal')->wherein('sydls_id', GetTeamMember($agent1, $v1['id']))->select()->toArray();
            $cs = array_column($cs, 'channel_id');
            $xi = array_merge($zh, $cs);
            //团队分润
            $agent2[$k1]['profit'] = Profit::where('agent_id', $user['id'])->where('terminal_id', 'in', $xi)->where('type', 1)->sum('profit');
            //直属商户
            $agent2[$k1]['commercial'] = Channel::where('dls_id', $v1['id'])->count();
            //直属代理商
            $agent2[$k1]['agent'] = SystemAdmin::where('higher_level_id', $v1['id'])->count();
            //下级团队流水
//            dump(array_column(get_downline($agent1, $v1['id']), 'id'));die;
            $agent2[$k1]['deal_money1'] = Deallist::wherein('dls_id', GetTeamMember($agent1, $v1['id']))->sum('deal_money');
            //本级流水
            $agent2[$k1]['deal_money2'] = Deallist::where('dls_id', $v1['id'])->sum('deal_money');
            $agent2[$k1]['deal_money'] = $agent2[$k1]['deal_money1'] + $agent2[$k1]['deal_money2'];
            //激活的台数
            $agent2[$k1]['jihuo_number'] = Channel::where('dls', 'like', '%' . $v1['id'] . '%')->where('merchant_code', '<>', '')->count();
            //总台数
            $agent2[$k1]['all_number'] = Channel::where('dls', 'like', '%' . $v1['id'] . '%')->count();
            //激活率
            if ($agent2[$k1]['all_number'] == 0 || $agent2[$k1]['jihuo_number'] == 0) {
                $agent2[$k1]['ratio'] = 0;
                $agent2[$k1]['avg'] = 0;
            } else {
                $agent2[$k1]['ratio'] = round($agent2[$k1]['jihuo_number'] / $agent2[$k1]['all_number'] * 100, 2);
                //台军交易
                $agent2[$k1]['avg'] = round($agent2[$k1]['deal_money'] / $agent2[$k1]['all_number'], 2);
            }

        }

        return Result::Success($agent2);
    }


    //代理商管理
    public function agent1(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //下级代理商数量

        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        //所有下级
//        $agent2 = get_downline($agent1, $user['id']);
        //直属
        $where = [];
        if (isset($req['search'])) {
            $where = [
                ['name', 'like', '%' . $req['search'] . '%']
            ];
        }
        $agent4 = SystemAdmin::field('id,name,name,phone,higher_level_id')->where('higher_level_id', $user['id'])->where($where)->select()->toArray();
       $c= array_column($agent4,'id');
         $a = GetTeamMember($agent1, $user['id']);
        $ag= array_diff($a,$c);
        $agent2 = SystemAdmin::field('id,name,phone,higher_level_id')->where('id','in',$ag)->where($where)->select()->toArray();
        //所有下级代理
        $col = [];
        foreach ($agent2 as $k1 => $v1) {
            $col[] = $v1;
            //直属分润

//            直属下级机器
            $zh = Db::name('channel_terminal')->where('sydls_id', $v1['id'])->select()->toArray();
            $zh = array_unique(array_column($zh, 'channel_id'));
            //           所有下级的机器
            $cs = Db::name('channel_terminal')->wherein('sydls_id', GetTeamMember($agent1, $v1['id']))->select()->toArray();
            $cs = array_column($cs, 'channel_id');
            $xi = array_merge($zh, $cs);
            //团队分润
            $agent2[$k1]['profit'] = Profit::where('agent_id', $user['id'])->where('terminal_id', 'in', $xi)->where('type', 1)->sum('profit');
            //直属商户
            $agent2[$k1]['commercial'] = Channel::where('dls_id', $v1['id'])->count();
            //直属代理商
            $agent2[$k1]['agent'] = SystemAdmin::where('higher_level_id', $v1['id'])->count();
            //下级团队流水
//            dump(array_column(get_downline($agent1, $v1['id']), 'id'));die;
            $agent2[$k1]['deal_money1'] = Deallist::wherein('dls_id', GetTeamMember($agent1, $v1['id']))->sum('deal_money');
            //本级流水
            $agent2[$k1]['deal_money2'] = Deallist::where('dls_id', $v1['id'])->sum('deal_money');
            $agent2[$k1]['deal_money'] = $agent2[$k1]['deal_money1'] + $agent2[$k1]['deal_money2'];
            //激活的台数
            $agent2[$k1]['jihuo_number'] = Channel::where('dls', 'like', '%' . $v1['id'] . '%')->where('merchant_code', '<>', '')->count();
            //总台数
            $agent2[$k1]['all_number'] = Channel::where('dls', 'like', '%' . $v1['id'] . '%')->count();
            //激活率
            if ($agent2[$k1]['all_number'] == 0 || $agent2[$k1]['jihuo_number'] == 0) {
                $agent2[$k1]['ratio'] = 0;
                $agent2[$k1]['avg'] = 0;
            } else {
                $agent2[$k1]['ratio'] = round($agent2[$k1]['jihuo_number'] / $agent2[$k1]['all_number'] * 100, 2);
                //台军交易
                $agent2[$k1]['avg'] = round($agent2[$k1]['deal_money'] / $agent2[$k1]['all_number'], 2);
            }

        }

        return Result::Success($agent2);
    }

//        链条查询        //所有下级

    public function chain(Request $request)
    {
        $req = $request->param();
        //验证
        $validate = new \think\Validate();
        $validate->rule([
            'search|搜索内容' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        $user = SystemAdmin::where('name', '=', $req['search'])->find();
        if (!$user) {
            return Result::Error('不存在', 1000);
        }
        $agent2 = $this->merchants_sub($user['id']);
        foreach ($agent2 as $k1 => $v1) {
            $col[] = $v1;

            //直属商户
            $agent2[$k1]['commercial'] = Channel::where('dls_id', $v1['id'])->count();
            //直属代理商
            $agent2[$k1]['agent'] = SystemAdmin::where('higher_level_id', $v1['id'])->count();
            //激活的台数
            $agent2[$k1]['jihuo_number'] = Channel::where('dls', 'like', '%' . $v1['id'] . '%')->where('merchant_code', '<>', '')->count();
            //总台数
            $agent2[$k1]['all_number'] = Channel::where('dls', 'like', '%' . $v1['id'] . '%')->count();
            //激活率
            if ($agent2[$k1]['all_number'] == 0 || $agent2[$k1]['jihuo_number'] == 0) {
                $agent2[$k1]['ratio'] = 0;
                $agent2[$k1]['avg'] = 0;
            } else {
                $agent2[$k1]['ratio'] = round($agent2[$k1]['jihuo_number'] / $agent2[$k1]['all_number'] * 100, 2);

            }

        }
//        dump($chain);die;
        return Result::Success($agent2);
    }

    //代理商政策详情
    public function agentdetails(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $validate = new \think\Validate();
        $validate->rule([
            'agent_id|代理商id' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        //获取改代理商所有机具政策关联信息
        $a = Db::name('channel_terminal')->where('sydls_id', $req['agent_id'])->select()->toArray();
        //结算政策
        $jeisuan = array_column($a, 'jiesuan_id');
        $jeisuan = array_unique($jeisuan);
        $jiesuan = Jiesuan::where('id', 'in', $jeisuan)->select();
        foreach ($jiesuan as $k=>$v)
        {
            $jiesuan[$k]['number_dd']=Db::name('channel_terminal')->where('sydls_id', $req['agent_id'])->where('jiesuan_id', $v['id'])->count();
        }
        //机具政策
        $jiju = Jiju::where('id', 'in', array_unique(array_column($a, 'jiju_id')))->select();
        //提现政策
        $tixian = Tixian::where('id', 'in', array_unique(array_column($a, 'tixian_id')))->select();
        return Result::Success([
            'jiesuan' => $jiesuan,
            'jiju' => $jiju,
            'tixian' => $tixian,
        ]);
    }

    //代理商团队

    public function team(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $validate = new \think\Validate();
        $validate->rule([
            'agent_id|代理商' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        //直属商户
        $agent['commercial'] = Channel::where('dls_id', $req['agent_id'])->count();
        //直属代理商
        $agent['agent'] = SystemAdmin::where('higher_level_id', $req['agent_id'])->count();
        //下级团队流水GetTeamMember($agent1, $v1['id'])
        $agent['deal_money1'] = Deallist::wherein('dls_id', GetTeamMember($agent1, $req['agent_id']))->sum('deal_money');
        //本级流水
        $agent['deal_money2'] = Deallist::where('dls_id', $req['agent_id'])->sum('deal_money');
        $agent['deal_money'] = $agent['deal_money1'] + $agent['deal_money2'];
        //激活的台数
        $agent['jihuo_number'] = Channel::where('dls', 'like', '%' . $req['agent_id'] . '%')->where('merchant_code', '<>', '')->count();
        //总台数
        $agent['all_number'] = Channel::where('dls', 'like', '%' . $req['agent_id'] . '%')->count();
        //激活率
        if ($agent['all_number'] == 0 || $agent['jihuo_number'] == 0) {
            $agent['ratio'] = 0;
            $agent['avg'] = 0;
        } else {
            $agent['ratio'] = round($agent['jihuo_number'] / $agent['all_number'] * 100, 2);
            //台军交易
            $agent['avg'] = round($agent['deal_money'] / $agent['all_number'], 2);
        }

        return Result::Success($agent);
    }

    //代理商机具
    public function machines(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $validate = new \think\Validate();
        $validate->rule([
            'agent_id|代理商id' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        //激活的台数
        $agent['jihuo_number'] = Channel::where('dls', 'like', '%' . $req['agent_id'] . '%')->where('merchant_code', '<>', '')->count();
        //总台数
        $agent['all_number'] = Channel::where('dls', 'like', '%' . $req['agent_id'] . '%')->count();
        //激活率
        if ($agent['all_number'] == 0 || $agent['jihuo_number'] == 0) {
            $agent['ratio'] = 0;
            $agent['avg'] = 0;
        } else {
            $agent['ratio'] = round($agent['jihuo_number'] / $agent['all_number'] * 100, 2);
        }
        //分型号
        $a = Terminancate::withcount(['channel' => function (Query $query) use ($req) {
            $query->where('dls', 'like', '%' . $req['agent_id'] . '%');
        }])->select()->toArray();
        foreach ($a as $k => $v) {
            //总已激活
            $a[$k]['return_activate'] = Channel::where('dls', 'like', '%' . $req['agent_id'] . '%')->where('cate_id', $v['id'])->where('return_activate', 1)->count();
            //已返现
            $a[$k]['retuen_reach'] = Channel::where('dls', 'like', '%' . $req['agent_id'] . '%')->where('cate_id', $v['id'])->where('retuen_reach', 1)->count();
            if ($a[$k]['return_activate'] == 0 || $v['channel_count'] == 0) {
                $a[$k]['ratio'] = 0;
            } else {
                $a[$k]['ratio'] = round($a[$k]['return_activate'] / $v['channel_count'] * 100, 2);
            }
        }

        return Result::Success(['cate' => $a, 'agent' => $agent]);
    }

    //代理商信息
    public function resume(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $validate = new \think\Validate();
        $validate->rule([
            'agent_id|代理商id' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }

        $user = SystemAdmin::where('id', $req['agent_id'])->find();
        return Result::Success($user);
    }

}