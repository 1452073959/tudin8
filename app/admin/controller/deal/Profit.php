<?php

namespace app\admin\controller\deal;

use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="deal_profit")
 */
class Profit extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\deal\Profit();

    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        $a = $this->request->param('id');
        $b = $this->request->param('filter');
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin(['dls', 'terminal.merchant'], 'LEFT')
                ->where($where)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->where('type', 1)
                ->where('agent_id', $this->admininfo()['id'])
                ->count();
            $list = $this->model
                ->where($where)
                ->where('agent_id', $this->admininfo()['id'])
                ->where('type', 1)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->withJoin(['dls', 'terminal'], 'LEFT')
                ->page($page, $limit)
                ->order($this->sort)
                ->select();

            $profit = $this->model
                ->where($where)
                ->where('agent_id', $this->admininfo()['id'])
                ->where('type', 1)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->sum('profit');

            $total = $this->model
                ->where($where)
                ->where('agent_id', $this->admininfo()['id'])
                ->where('type', 1)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->sum('amount');
            $data = [
                'code' => 0,
                'msg' => '',
                'count' => $count,
                'data' => $list,
                'totalRow' => ['deal_money' => $total, 'profit' => $profit],
            ];
            return json($data);
        }
        $this->assign('a', $a);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="代理分润记录")
     */
    public function dl()
    {
        $a = $this->request->param('id');
        $b = $this->request->param('filter');
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();

            $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
            //所有下级
            $agent2 = GetTeamMember($agent1, $this->admininfo()['id']);
            $count = $this->model
                ->withJoin(['dls', 'terminal.merchant'], 'LEFT')
                ->where($where)
                ->where('type', 1)
                ->where('agent_id', 'in', $agent2)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->count();
            $list = $this->model
                ->where($where)
                ->where('type', 1)
                ->withJoin(['dls', 'terminal'], 'LEFT')
                ->where('agent_id', 'in', $agent2)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->page($page, $limit)
                ->order($this->sort)
                ->select();

            $profit = $this->model
                ->where($where)
                ->where('type', 1)
                ->withJoin(['dls', 'terminal'], 'LEFT')
                ->where('agent_id', 'in', $agent2)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->sum('profit');

            $total = $this->model
                ->where($where)
                ->where('type', 1)
                ->withJoin(['dls', 'terminal'], 'LEFT')
                ->where('agent_id', 'in', $agent2)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->sum('amount');
            $data = [
                'code' => 0,
                'msg' => '',
                'count' => $count,
                'data' => $list,
                'totalRow' => ['deal_money' => $total, 'profit' => $profit],
            ];
            return json($data);
        }
        $this->assign('a', $a);
        return $this->fetch('index');
    }


    /**
     * @NodeAnotation(title="代理分润记录,单个")
     */
    public function agent_profit()
    {
        $a = $this->request->param('id');
        $b = $this->request->param('filter');
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin(['dls', 'terminal.merchant'], 'LEFT')
                ->where($where)
                ->where('agent_id', '=', $a)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->count();
            $list = $this->model
                ->where($where)
                ->withJoin(['dls', 'terminal'], 'LEFT')
                ->where('agent_id', '=', $a)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->page($page, $limit)
                ->order($this->sort)
                ->select();

            $profit = $this->model
                ->where($where)
                ->where('agent_id', '=', $a)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->sum('profit');

            $total = $this->model
                ->where($where)
                ->where('agent_id', '=', $a)
                ->when(!$b, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('createtime', 'today');
                })
                ->sum('amount');
            $data = [
                'code' => 0,
                'msg' => '',
                'count' => $count,
                'data' => $list,
                'totalRow' => ['deal_money' => $total, 'profit' => $profit],
            ];
            return json($data);
        }
        $this->assign('a', $a);
        return $this->fetch('index');
    }

    /**
     * @NodeAnotation(title="代理返现记录")
     */
    public function dlfx()
    {
        $a = $this->request->param('id');
        $b = $this->request->param('filter');
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();

            $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
            //所有下级
            $agent2 = GetTeamMember($agent1, $this->admininfo()['id']);
            $count = $this->model
                ->withJoin(['dls', 'terminal.merchant'], 'LEFT')
                ->where($where)
                ->where('type', 2)
                ->where('agent_id', 'in', $agent2)
                ->count();
            $list = $this->model
                ->where($where)
                ->where('type', 2)
                ->withJoin(['dls', 'terminal'], 'LEFT')
                ->where('agent_id', 'in', $agent2)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();

//            dump($list->toArray());die;
            $data = [
                'code' => 0,
                'msg' => '',
                'count' => $count,
                'data' => $list,
            ];
            return json($data);
        }
        $this->assign('a', $a);
        return $this->fetch('index');
    }

}