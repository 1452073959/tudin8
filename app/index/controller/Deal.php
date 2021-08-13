<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/22
 * Time: 17:32
 */

namespace app\index\controller;

use app\admin\help\Result;
use app\admin\model\deal\Deallist;
use app\admin\model\deal\Profit;
use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;
use app\ApiController;
use app\Request;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use think\db\Query;
use  think\facade\Db;

class Deal extends ApiController
{
    protected $middleware = ['\app\middleware\Check::class'];

    public function index(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $jhts = Channel::where('dls', 'like', '%' . $user['id'] . '%')->where('merchant_code', '<>', '')->field('id,sn')->select();
        $arr = [];
        foreach ($jhts as $k => $v) {
            $arr[] = $v['id'];
        }
        //团队交易总金额
        $teamtotal = Deallist::where('terminal_id', 'in', $arr)->sum('deal_money');
//        累计收益
        $profit = Profit::where('agent_id', $user['id'])->sum('profit');
        //当前余额
        $total = SystemAdmin::where('id', $user['id'])->value('profit_balance') + SystemAdmin::where('id', $user['id'])->value('return_balance');
        return Result::Success(['teamtotal' => $teamtotal, 'profit' => $profit, 'total' =>round($total,2) ]);

    }

    //返现/分润西
    public function dealdetail(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();

        $validate = new \think\Validate();
        $validate->rule([
            'type|类型' => 'require|number',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        //当天开始时间
        $start_time = strtotime(date("Y-m-d", time()));
        //当天结束之间
        $end_time = $start_time + 60 * 60 * 24;
//        $firstTime = \think\facade\Request::post('firstTime', $start_time);
//        $lastTime = \think\facade\Request::post('lastTime', $end_time);
        if ($req['firstTime'] == '') {
            $firstTime = $start_time;
        } else {
            $firstTime = $req['firstTime'];
        }

        if ($req['lastTime'] == '') {
            $lastTime = $end_time;
        } else {
            $lastTime = $req['lastTime'];
        }
        $data = Profit::with(['terminal' => function (Query $query) {
            $query->with(['cate']);
        }, 'deal'])->where('agent_id', $user['id'])->whereBetweenTime('createtime', $firstTime, $lastTime)->where('type', $req['type'])->order('id', 'desc')->select();

        return Result::Success($data);
    }

    //我的收益
    public function my_earnings(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //当天开始时间
        $start_time = strtotime(date("Y-m-d", time()));
        //当天结束之间
        $end_time = $start_time + 60 * 60 * 24;
        //当天收益
        $today = Profit::where('agent_id', $user['id'])->whereBetweenTime('createtime', $start_time, $end_time)->sum('profit');
        //累计收益
        $profit = Profit::where('agent_id', $user['id'])->sum('profit');
        //当前余额
        $total = SystemAdmin::where('id', $user['id'])->value('profit_balance') + SystemAdmin::where('id', $user['id'])->value('return_balance');
        //分润收益
        $shareprofit = Profit::where('agent_id', $user['id'])->where('type', 1)->sum('profit');
        //返现收益
        $returnprofit = Profit::where('agent_id', $user['id'])->where('type', 2)->sum('profit');
        return Result::Success([
            'today' => $today,     //当天收益
            'profit' => $profit,   //累计收益
            'total' => $total,  //当前余额
            'shareprofit' => $shareprofit,    //分润收益
            'returnprofit' => $returnprofit,   //返现收益
        ]);
    }

}