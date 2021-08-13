<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/23
 * Time: 11:25
 */

namespace app\index\controller;

use app\admin\help\Result;
use app\admin\model\template\Jiesuan;
use app\admin\model\template\Jiju;
use app\admin\model\template\Tixian;
use app\ApiController;
use app\Request;
use think\db\Query;
use  think\facade\Db;

class Template extends ApiController
{
    protected $middleware = ['\app\middleware\Check::class'];

    //结算模板
    public function jiesuan_template(Request $request)
    {
        $user = $this->user($request);
        $data = Jiesuan::where('agent_id', $user['id'])->select();
        return Result::Success($data);
    }

    //机具模板
    public function jiju_template(Request $request)
    {
        $user = $this->user($request);
        if ($user['auth_ids'] == 8) {
            $up = $this->merchants_sub($user['higher_level_id']);
            $a = array_column($up, 'id');
            $data = Jiju::where('agent_id', array_pop($a))->select();
        } else {
            $data = Jiju::where('agent_id', $user['id'])->select();
        }

        return Result::Success($data);
    }

    //提现模板
    public function tixian_template(Request $request)
    {
        $user = $this->user($request);
        if ($user['auth_ids'] == 8) {
            $up = $this->merchants_sub($user['higher_level_id']);
            $a = array_column($up, 'id');
            $data = Tixian::where('agent_id', array_pop($a))->select();
        } else {
            $data = Tixian::where('agent_id', $user['id'])->select();
        }
        return Result::Success($data);
    }

    //添加结算模板
    public function jiesuan_add(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        //验证
        $validate = new \think\Validate();
        $validate->rule([
            'tname|该模板名称' => 'require|unique:template_jiesuan',
            'cFeeRate' => 'require',
            'dFeeRate' => 'require',
            'dFeeMax' => 'require',
            'wechatPayFeeRate' => 'require',
            'alipayFeeRate' => 'require',
            'ycFreeFeeRate' => 'require',
            'ydFreeFeeRate' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        $templte = new Jiesuan();
        $templte->agent_id = $user['id'];
        $templte->tname = $req['tname'];
        $templte->cFeeRate = $req['cFeeRate'];
        $templte->dFeeRate = $req['dFeeRate'];
        $templte->dFeeMax = $req['dFeeMax'];
        $templte->wechatPayFeeRate = $req['wechatPayFeeRate'];
        $templte->alipayFeeRate = $req['alipayFeeRate'];
        $templte->ycFreeFeeRate = $req['ycFreeFeeRate'];
        $templte->ydFreeFeeRate = $req['ydFreeFeeRate'];
        $templte->save();

        return Result::Success($templte, '创建成功');
    }

    //添加机具模板
    public function jiju_add(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        if ($user['auth_ids'] == 8) {
            return Result::Error(1000, '只有一级代理商才可创建');
        }

        //验证
        $validate = new \think\Validate();
        $validate->rule([
            'tname|该模板名称' => 'require|unique:template_jiju',
            'activity_condition' => 'require',
            'activity_return' => 'require',
            'activity_false' => 'require',
            'activity_false_withhold' => 'require',
            'activity_time' => 'require',
            'reach_condition' => 'require',
            'reach_return' => 'require',
            'reach_time' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        $templte = new Jiju();
        $templte->agent_id = $user['id'];
        $templte->tname = $req['tname'];
        $templte->activity_condition = $req['activity_condition'];
        $templte->activity_return = $req['activity_return'];
        $templte->activity_false = $req['activity_false'];
        $templte->activity_false_withhold = $req['activity_false_withhold'];
        $templte->activity_time = $req['activity_time'];
        $templte->reach_condition = $req['reach_condition'];
        $templte->reach_return = $req['reach_return'];
        $templte->reach_time = $req['reach_time'];
        $templte->sim_time = $req['sim_time'];
        $templte->sim_return = $req['sim_return'];
        $templte->sim_service = $req['sim_service'];
        $templte->activity_service = $req['activity_service'];
        $templte->save();
        return Result::Success($templte, '创建成功');
    }

    //添加提现模板
    public function tixian_add(Request $request)
    {
        $user = $this->user($request);

        if ($user['auth_ids'] == 8) {
            return Result::Error(1000, '只有一级代理商才可创建');
        }
        $req = $request->param();
        //验证
        $validate = new \think\Validate();
        $validate->rule([
            'tname|该模板名称' => 'require|unique:template_tixian',
            'fenrun_rate' => 'require',
            'fenrun_sxf' => 'require',
            'jijufan_rate' => 'require',
            'jijufan_sxf' => 'require',
            'fwf_rate' => 'require',
            'fwf_sxf' => 'require',
        ]);
        if (!$validate->check($req)) {
            return Result::Error('1000', $validate->getError());
        }
        $templte = new Tixian();
        $templte->agent_id = $user['id'];
        $templte->tname = $req['tname'];
        $templte->fenrun_rate = $req['fenrun_rate'];
        $templte->fenrun_sxf = $req['fenrun_sxf'];
        $templte->jijufan_rate = $req['jijufan_rate'];
        $templte->jijufan_sxf = $req['jijufan_sxf'];
        $templte->fwf_rate = $req['fwf_rate'];
        $templte->fwf_sxf = $req['fwf_sxf'];
        $templte->save();
        return Result::Success($templte, '创建成功');
    }

    //结算模板
    public function min_max(Request $request)
    {
        $user = $this->user($request);
        $yushe = Jiesuan::where('id', 1)->select();
        if ($user['auth_ids'] == 8) {
            //获取改代理商所有机具政策关联信息
            $a = Db::name('channel_terminal')->where('sydls_id', $user['id'])->select()->toArray();
            //结算政策
            $jeisuan = array_column($a, 'jiesuan_id');
            $jeisuan = array_unique($jeisuan);
            $yushe = Jiesuan::where('id', 'in', $jeisuan)->select();
        }

        return Result::Success($yushe);
    }

    //删除结算
    public function del_jiesuan(Request $request)
    {
        $user = $this->user($request);
        if ($user['auth_ids'] == 8) {
            return Result::Error(1000, '只有一级代理商才可删除');
        }
        $req = $request->param();
        $res = Jiesuan::destroy($req['id']);
        if ($res) {
            return Result::Success('删除成功');
        } else {
            return Result::Error('失败', 1000);
        }
    }

    //删除机具
    public function del_jiju(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $res = Jiju::destroy($req['id']);
        if ($res) {
            return Result::Success('删除成功');
        } else {
            return Result::Error('失败', 1000);
        }
    }

    //删除提现
    public function del_tixian(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        if ($user['auth_ids'] == 8) {
            return Result::Error(1000, '只有一级代理商才可删除');
        }
        $res = Tixian::destroy($req['id']);
        if ($res) {
            return Result::Success('删除成功');
        } else {
            return Result::Error('失败', 1000);
        }
    }

    //编辑结算
    public function edit_jiesuan(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        $templte = Jiesuan::find($req['id']);
        $templte->cFeeRate = $req['cFeeRate'];
        $templte->dFeeRate = $req['dFeeRate'];
        $templte->dFeeMax = $req['dFeeMax'];
        $templte->wechatPayFeeRate = $req['wechatPayFeeRate'];
        $templte->alipayFeeRate = $req['alipayFeeRate'];
        $templte->ycFreeFeeRate = $req['ycFreeFeeRate'];
        $templte->ydFreeFeeRate = $req['ydFreeFeeRate'];
        $res = $templte->save();
        if ($res) {
            return Result::Success('成功');
        } else {
            return Result::Error('失败', 1000);
        }
    }


    //编辑机具
    public function edit_jiju(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        if ($user['auth_ids'] == 8) {
            return Result::Error(1000, '只有一级代理商才可编辑');
        }
        $templte = Jiju::find($req['id']);
        $templte->activity_condition = $req['activity_condition'];
        $templte->activity_return = $req['activity_return'];
        $templte->activity_false = $req['activity_false'];
        $templte->activity_false_withhold = $req['activity_false_withhold'];
        $templte->activity_time = $req['activity_time'];
        $templte->reach_condition = $req['reach_condition'];
        $templte->reach_return = $req['reach_return'];
        $templte->reach_time = $req['reach_time'];
        $templte->sim_time = $req['sim_time'];
        $templte->sim_return = $req['sim_return'];
        $templte->sim_service = $req['sim_service'];
        $templte->activity_service = $req['activity_service'];
        $res = $templte->save();
        if ($res) {
            return Result::Success('成功');
        } else {
            return Result::Error('失败', 1000);
        }
    }

    //tixian编辑
    public function edit_tixian(Request $request)
    {
        $user = $this->user($request);
        $req = $request->param();
        if ($user['auth_ids'] == 8) {
            return Result::Error(1000, '只有一级代理商才可编辑');
        }
        $templte = Tixian::find($req['id']);
        $templte->fenrun_rate = $req['fenrun_rate'];
        $templte->fenrun_sxf = $req['fenrun_sxf'];
        $templte->jijufan_rate = $req['jijufan_rate'];
        $templte->jijufan_sxf = $req['jijufan_sxf'];
        $templte->fwf_rate = $req['fwf_rate'];
        $templte->fwf_sxf = $req['fwf_sxf'];
        $res = $templte->save();
        if ($res) {
            return Result::Success('成功');
        } else {
            return Result::Error('失败', 1000);
        }
    }


}