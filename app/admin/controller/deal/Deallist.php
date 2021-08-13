<?php

namespace app\admin\controller\deal;

use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="流水")
 */
class Deallist extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\deal\Deallist();
        
    }


    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {

        $a = $this->request->param('filter');
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            $agent1 = SystemAdmin::field('id,name,phone,higher_level_id')->select()->toArray();
            //所有下级
            $agent2 = GetTeamMember($agent1, $this->admininfo()['id']);
            $agent2[]=$this->admininfo()['id'];
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin(['dls'], 'LEFT')
                ->where($where)
                ->where('dls_id', 'in',$agent2)
                ->when(!$a, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('deal_create_time', 'today');
                })
//                ->whereOr('top_code_deal', $this->admininfo()['appid'])
                ->count();
            $list = $this->model
                ->where($where)
                ->where('dls_id', 'in',$agent2)
                ->when(!$a, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('deal_create_time', 'today');
                })
//                ->whereOr('top_code_deal', $this->admininfo()['appid'])
                ->withJoin(['dls',], 'LEFT')
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $total = $this->model
                ->where($where)
                ->where('dls_id', 'in',$agent2)
                ->when(!$a, function ($query) use ($a) {
                    // 满足条件后执行
                    return $query->whereTime('deal_create_time', 'today');
                })
                ->sum('deal_money');
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow'  => ['deal_money'=>$total],
            ];
            return json($data);
        }

        return $this->fetch();
    }

    
}