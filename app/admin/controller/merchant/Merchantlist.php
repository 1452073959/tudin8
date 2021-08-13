<?php

namespace app\admin\controller\merchant;

use app\admin\controller\terminal\Channel;
use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="商户")
 */
class Merchantlist extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\terminal\Channel();
        
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin(['dls','cate'], 'LEFT')
                ->where($where)
                ->where('dls','like','%'.$this->admininfo()['id'].'%')
                ->where('merchant_code','<>','')
                ->count();
            $list = $this->model
                ->where($where)
                ->where('dls','like','%'.$this->admininfo()['id'].'%')
                ->where('merchant_code','<>','')
                ->withJoin(['dls','cate'], 'LEFT')
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }


}