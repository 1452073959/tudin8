<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/28
 * Time: 9:31
 */

namespace app\index\controller;

use app\admin\help\Result;
use app\admin\model\deal\Deallist;
use app\admin\model\deal\Profit;
use app\admin\model\deal\Withdrawal;
use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;
use app\admin\model\terminal\Terminancate;
use app\ApiController;
use app\Request;
use think\db\Query;
use think\facade\Db;

//活动和其他
class Activity extends ApiController
{
    protected $middleware = ['\app\middleware\Check::class'];

    //分配
    public function activity(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();

        $cate = Terminancate::with(['channel' => function (Query $query) use ($user) {
            //一级
            $query->field('id,cate_id,retuen_reach,return_activate,activity,jiju_id')->where('dls', 'like', '%' . $user['id'] . '%')->where('activity', 2)->with('jiju');
        }])->select();


        //激活返现数量
        $num1 = 0;
        //收款达标数;
        $num2 = 0;
        //机具总数
        $num = 0;
        //目标金额
        $target = 0;
        //已完成金额
        $completed = 0;
        $completed1 = 0;
        $completed2 = 0;
        foreach ($cate as $k => $v) {
            //目标
            $cate[$k]['target'] = 0;
            //已完成
            $cate[$k]['completed'] = 0;
            $num += count($v['channel']);

            foreach ($v['channel'] as $k1 => $v1) {
                if ($v1['activity'] == 2) {
                    //激活返现金额
                    $cate[$k]['target'] += $v1['jiju']['activity_return'];
                    //达标返现
                    $cate[$k]['target'] += $v1['jiju']['reach_return'];
                    $target = $cate[$k]['target'];
                    //已达标返现
                    if ($v1['retuen_reach'] == 1) {
                        $num2 += 1;
                        $cate[$k]['completed'] += $v1['jiju']['reach_return'];
                        $completed1 += $v1['jiju']['reach_return'];

                    }
                    //已激活返现金额
                    if ($v1['return_activate'] == 1) {
                        $num1 += 1;
                        $cate[$k]['completed'] += $v1['jiju']['activity_return'];
                        $completed2 += $v1['jiju']['activity_return'];
                    }


                }
            }
        }

        return Result::Success([
            'num' => $num, 'num1' => $num1, 'num2' => $num2, 'target' => $target, 'completed' => $completed1 + $completed2, 'cate' => $cate
        ]);
    }

    //分配记录
    public function allocation(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();

        $data = Db::name('terminal_action')->where('cate_id', $req['cate_id'])->alias('a')
            ->leftJoin('terminal_cate w', 'w.id=a.cate_id')->field('a.id,cate_id,create_time,type,brand,sn')
            ->select()->toArray();

        foreach ($data as $k => $v) {
            //目标金额
            $data[$k]['target'] = 0;
            $data[$k]['completed'] = 0;
            $data[$k]['num1'] = 0;
            $data[$k]['num2'] = 0;
            foreach (explode(",", $v['sn']) as $k1 => $v1) {
                $a1 = Channel::with('jiju')->where('id', $v1)->find();
                $data[$k]['target'] += ($a1['jiju']['activity_return'] + $a1['jiju']['reach_return']);
                if ($a1['return_activate'] == 1) {
                    $data[$k]['num1'] += $a1['jiju']['activity_return'];
                }
                if ($a1['retuen_reach'] == 1) {
                    $data[$k]['num2'] += $a1['jiju']['reach_return'];
                }

            }
            $data[$k]['completed'] = $data[$k]['num1'] + $data[$k]['num2'];
        }


        return Result::Success($data);
    }

    //记录机器详情
    public function machines(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $data = Db::name('terminal_action')->where('id', $req['id'])->find();
        $where = [];
        $where1 = [];
//        $req['search']
        if (isset($req['search'])) {
            $where = function ($query) use ($req) {
                $query->where('sn', 'like', '%' . $req['search'] . '%')
                    ->whereOr('corporate_name', 'like', '%' . $req['search'] . '%');
            };
        }
        if (isset($req['Sizer'])) {

            if ($req['Sizer'] == 2) {
                $where1 = [
                    ['retuen_reach', '=', '2']
                ];
            } elseif ($req['Sizer'] == 1) {
                $where1 = [
                    ['return_activate', '=', '1']
                ];
            } elseif ($req['Sizer'] == 3) {
                $where1 = [
                    ['merchant_code', '<>', '']
                ];
            } else {
                $where1 = [];
            }

        }


        $array = Channel::with('jiju')->where('id', 'in', $data['sn'])->where($where)->where($where1)->select()->toArray();
        foreach ($array as $k1 => $v1) {
            //激活截止
            $array[$k1]['jhjz'] = date('Y-m-d', $v1['activation_time'] + $v1['jiju']['activity_time'] * 86400);
            //达标截止
            $array[$k1]['dbjz'] = date('Y-m-d', $v1['reach_time'] + $v1['jiju']['reach_time'] * 86400);
            //激活时间
            $array[$k1]['activation_time'] = date('Y-m-d', $array[$k1]['activation_time']);
            //达标时间
            $array[$k1]['reach_time'] = date('Y-m-d', $array[$k1]['reach_time']);
            //激活返现时间
            $array[$k1]['return_activate_time'] = date('Y-m-d', $array[$k1]['return_activate_time']);
            //达标返现时间
            $array[$k1]['retuen_reach_time'] = date('Y-m-d', $array[$k1]['commercial_time']);
            //商户激活时间
            $array[$k1]['commercial_time'] = date('Y-m-d', $array[$k1]['commercial_time']);


        }

        return Result::Success($array);

    }


    //未划拨机器详情

    public function unallotted(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $where = [];
        $where1 = [];
        if (isset($req['search'])) {
            $where = function ($query) use ($req) {
                $query->where('sn', 'like', '%' . $req['search'] . '%')
                    ->whereOr('corporate_name', 'like', '%' . $req['search'] . '%');
            };
        }

        if (isset($req['Sizer'])) {

            if ($req['Sizer'] == 2) {
                $where1 = [
                    ['retuen_reach', '=', '1']
                ];
            } elseif ($req['Sizer'] == 1) {
                $where1 = [
                    ['return_activate', '=', '1']
                ];
            } elseif ($req['Sizer'] == 3) {
                $where1 = [
                    ['merchant_code', '<>', '']
                ];
            } else {
                $where1 = [];
            }

        }
        $array = Channel::with('jiju')->where('dls', 'like', '%' . $user['id'] . '%')->where('activity', 2)->where($where)->where($where1)->select()->toArray();
        return Result::Success($array);
    }

    //本日
    public function today(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //本日分润
        $profit = Profit::where('agent_id', $user['id'])->whereTime('createtime', 'today')->where('type', 1)->sum('profit');
        //        获取所有下级代理商
        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        $agent2 = GetTeamMember($agent1, $user['id']);
        $agent2[] = $user['id'];
//        dump($agent2);die;
        //本日流水
        $deal = Deallist::where('dls_id', 'in', $agent2)->whereTime('deal_create_time', 'today')->sum('deal_money');
        //交易笔数
        $count = Deallist::where('dls_id', 'in', $agent2)->whereTime('deal_create_time', 'today')->count();
        //新增图钉
        $agent = SystemAdmin::whereTime('create_time', 'today')->count();
        //新增商户
        $commercial = Channel::whereTime('commercial_time', 'today')->count();
        return Result::Success(['profit' => $profit, 'deal' => $deal, 'count' => $count, 'agent' => $agent, 'commercial' => $commercial
        ]);
    }

    //近一周/半年
    public function statistical(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //最近七天
        $w = get_weeks();
        //        获取所有下级代理商
        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        $agent2 = GetTeamMember($agent1, $user['id']);
        $agent2[] = $user['id'];
        $arr_w = array();
        foreach (array_reverse($w) as $k => $v) {
            $arr_w[$k]['date'] = $v;
            $arr_w[$k]['deal'] = Deallist::where('dls_id', 'in', $agent2)->whereDay('deal_create_time', $v)->sum('deal_money');
            $arr_w[$k]['deal_count'] = Deallist::where('dls_id', 'in', $agent2)->whereDay('deal_create_time', $v)->count();
            $arr_w[$k]['prant'] = Profit::where('agent_id', $user['id'])->whereDay('createtime', $v)->where('type', 1)->sum('profit');
            $arr_w[$k]['prant_count'] = Profit::where('agent_id', $user['id'])->whereDay('createtime', $v)->where('type', 1)->count();
        }
        //半年月份
        $m = monthlater();
        $arr_m = array();
        foreach (array_reverse($m) as $k => $v) {
            $arr_m[$k]['date'] = $v;
            $arr_m[$k]['deal'] = Deallist::where('dls_id', 'in', $agent2)->whereMonth('deal_create_time', $v)->sum('deal_money');
            $arr_m[$k]['deal_count'] = Deallist::where('dls_id', 'in', $agent2)->whereMonth('deal_create_time', $v)->count();
            $arr_m[$k]['prant'] = Profit::where('agent_id', $user['id'])->whereMonth('createtime', $v)->where('type', 1)->sum('profit');
            $arr_m[$k]['prant_count'] = Profit::where('agent_id', $user['id'])->whereMonth('createtime', $v)->where('type', 1)->count();
        }

        return Result::Success([
            'w' => $arr_w,
            'm' => $arr_m,
        ]);
    }

    //提现申请
    public function application(Request $request)
    {
        $user = $this->user($request);

        $req = $request->param();
        $validate = new \think\Validate();
        $validate->rule([
            'name|姓名' => 'require',
            'account|账号' => 'require',
            'money|金额' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }

//        if($req['money']>$user){
//            return Result::Error('1000','')
//        }
        $application = new Withdrawal();
        $application->name = $req['name'];
        $application->account = $req['account'];
        $application->money = $req['money'];
        $application->save();
        if ($application) {
            return Result::Success('申请成功');
        } else {
            return Result::Error(1000, '错误');
        }

    }

    //修改资料
    public function information(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        if (isset($req['password'])) {
            $req['password'] = password($req['password']);
        }

        $user = SystemAdmin::find($user['id']);
        // save方法第二个参数为更新条件
        $user = $user::update($req, ['id' => $user['id']]);
        if ($user) {
            return Result::Success($user);
        } else {
            return Result::Error('失败', 1000);
        }
    }

    //新增代理
    public function addnum(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //直推图钉
        $num = SystemAdmin::field('id,name,phone,higher_level_id')->where('higher_level_id', $user['id'])->count();
        $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
        //间推图钉
        $agent4 = SystemAdmin::field('id,name,name,phone,higher_level_id')->where('higher_level_id', $user['id'])->select()->toArray();
        $c = array_column($agent4, 'id');
        $a = GetTeamMember($agent1, $user['id']);
        $ag = array_diff($a, $c);


        $num1 = count($ag);
//        $num1 = 0;
//        foreach ($agent1 as $k => $v) {
//            $num1 += count(GetTeamMember($agent1, $v));
//        }
        //直推商户
        $num2 = Channel::where('dls_id', $user['id'])->where('merchant_code', '<>', '')->count();
        //间推商户
        $n = Channel::where('dls', 'like', '%' . $user['id'] . '%')->where('merchant_code', '<>', '')->count();
        $num3 = $n - $num2;
        return Result::Success([
            'ztdl' => $num,
            'jtdl' => $num1,
            'ztsh' => $num2,
            'jysh' => $num3,
        ]);
    }

    //base64图片
    public function qrcode(Request $request)
    {
        $user = $this->user($request);
        if ($user['auth_ids'] == 8) {
            $up = $this->merchants_sub($user['higher_level_id']);
            $a = array_column($up, 'id');
            $user = SystemAdmin::where('id', array_pop($a))->find();
        }
        $req = $request->param();
//        $file = file_get_contents($user['image']);
//        $img = base64_encode($file);
//        dump($user['image']);die;

        return Result::Success(['base64img' => $user['image']]);
    }

    //注册
    public function code(Request $request)
    {

        $user = $this->user($request);
        $req = $request->param();
        if ($user['auth_ids'] == 8 && $user['pushing_code'] == '') {
            $up = $this->merchants_sub($user['higher_level_id']);
            $a = array_column($up, 'id');
            $user = SystemAdmin::where('id', array_pop($a))->find();
        }

        $code = tudincode('https://' . $_SERVER['HTTP_HOST'] . '/scanCode/scan_code.html?' . 'code=' . $user['pushing_code']);
//        $code = 'https://' . $_SERVER['HTTP_HOST'] . '/scanCode/scan_code.html?' . 'code=' . $user['pushing_code'];
        return Result::Success(['base64img' => $code]);

    }

    //
    public function rest(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //注册成功
        $a = SystemAdmin::count();
        //实名认证
        $b = SystemAdmin::where('name', '<>', '')->count();
//        机具激活数量
        $c = Channel::where('dls', 'like', '%' . $user['id'] . '%')->where('merchant_code', '<>', '')->count();
        //流水过完机具数量
        $d = Channel::where('dls', 'like', '%' . $user['id'] . '%')->where('merchant_code', '<>', '')->where('deal_sum', '>', 10000)->count();
        //流水过完机具数量

        return Result::Success([
            'register' => $a,
            'autonym' => $b,
            'activate' => $c,
            'exceed' => $d,
        ]);
    }


}