<?php

namespace app\admin\controller\terminal;


use app\admin\help\Apirequest;
use app\admin\help\Result;
use app\admin\model\SystemAdmin;
use app\admin\model\template\Jiesuan;
use app\admin\model\template\Jiju;
use app\admin\model\template\Tixian;
use app\admin\model\terminal\Terminancate;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="终端")
 */
class Channel extends AdminController
{

    use \app\admin\traits\Curd;
    protected $relationSerach = true;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\terminal\Channel();

        $this->assign('getStatusList', $this->model->getStatusList());

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
                ->where('dls', 'find in set', $this->admininfo()['id'])
                ->withJoin(['jiesuan', 'jiju', 'tixian', 'dls', 'cate'], 'LEFT')
                ->where($where)
                ->count();
            $list = $this->model
                ->where($where)
                ->where('dls', 'find in set', $this->admininfo()['id'])
                ->withJoin(['jiesuan', 'jiju', 'tixian', 'dls', 'cate'], 'LEFT')
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code' => 0,
                'msg' => '',
                'count' => $count,
                'data' => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="添加")
     */
    public function add()
    {

        $jiju = Jiju::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $tixian = Tixian::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $jiesuan = Jiesuan::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $b = Terminancate::field('brand,id')->select();
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            // 启动事务
            Db::startTrans();
            try {
                $post['top_code'] = $this->admininfo()['appid'];
                $post['dls_id'] = $this->admininfo()['id'];
                $post['dls'] = $this->admininfo()['id'];
//                dump($this->admininfo());die;
                $save = $this->model->create($post);
//                dump($save->toArray());die;
                Db::name('channel_terminal')->insert(['channel_id' => $save['id'],
                    'sydls_id' => $this->admininfo()['id'],
                    'form_id' => $this->admininfo()['id'],
                    'jiesuan_id' => 1,
                    'jiju_id' => 1,
                    'tixian_id' => 1,
                ]);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                $this->error('保存失败:' . $e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign(['jiju' => $jiju]);
        $this->assign(['tixian' => $tixian]);
        $this->assign(['jiesuan' => $jiesuan]);
        $this->assign(['b' => $b]);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="编辑")
     */
    public function edit($id)
    {
        $row = $this->model->find($id);
        //自己的模板
        $b = Terminancate::field('brand,id')->select();

        $tixian = Tixian::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $jiesuan = Jiesuan::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $jiju = Jiju::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();

        empty($row) && $this->error('数据不存在');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        $this->assign(['b' => $b]);
        $this->assign(['tixian' => $tixian, 'jiesuan' => $jiesuan, 'jiju' => $jiju]);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="机器划拨")
     */

    public function grant()
    {
        $data = $this->request->get();
        $admin = $this->admininfo();
        //下级代理商
        $dls = SystemAdmin::where('higher_level_id', $this->admininfo()['id'])->select();
        $a = array();
        foreach ($dls as $k => $v) {
            $a[$k]['name'] = $v['name'];
            $a[$k]['value'] = $v['id'];
        }
        $tixian = Tixian::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $b = array();
        foreach ($tixian as $k => $v) {
            $b[$k]['name'] = $v['tname'];
            $b[$k]['value'] = $v['id'];
        }
        $jiesuan = Jiesuan::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $c = array();
        foreach ($jiesuan as $k => $v) {
            $c[$k]['name'] = $v['tname'];
            $c[$k]['value'] = $v['id'];
        }
        $jiju = Jiju::where('agent_id', $this->admininfo()['id'])->field('tname,id')->select();
        $d = array();
        foreach ($jiju as $k => $v) {
            $d[$k]['name'] = $v['tname'];
            $d[$k]['value'] = $v['id'];
        }
        $this->assign(['tixian' => $b, 'jiesuan' => $c, 'dataid' => $data['id'], 'jiju' => $d]);
        $this->assign('dls', $a);
        $this->assign('admin', $admin);

        return $this->fetch();
    }


    /**
     * @NodeAnotation(title="机器划拨")
     */
    public function granthb()
    {
        $post1 = $this->request->post();

        $val = explode(",", $post1['dataid']);
//       $a= \app\admin\model\terminal\Channel::where('id','in',$val)->select()->toArray();
        try {
            // 启动事务
            Db::startTrans();
            foreach ($val as $k => $v) {
                $terminal = \app\admin\model\terminal\Channel::with(['jiesuan'])->find($v);
                $new = Jiesuan::find($post1['jiesuan_id'])->toArray();
                if (
                    $terminal['jiesuan']['cFeeRate'] >= $new['cFeeRate'] ||
                    $terminal['jiesuan']['dFeeRate'] >= $new['dFeeRate'] ||
                    $terminal['jiesuan']['dFeeMax'] >= $new['dFeeMax'] ||
                    $terminal['jiesuan']['wechatPayFeeRate'] >= $new['wechatPayFeeRate'] ||
                    $terminal['jiesuan']['alipayFeeRate'] >= $new['alipayFeeRate'] ||
                    $terminal['jiesuan']['ycFreeFeeRate'] >= $new['ycFreeFeeRate'] ||
                    $terminal['jiesuan']['ydFreeFeeRate'] >= $new['ydFreeFeeRate']

                ) {
                    echo "<script>alert('选择模板的费率小于上级模板')</script>";
                    echo "<script>window.parent.location.reload()</script>";
                    // 回滚事务
                    Db::rollback();
                    return;
                }

                if (
                    $terminal['dls_id'] != $this->admininfo()['id']
                ) {
                    continue;
                }


                $terminal->dls_id = $post1['dls_id'];
                $terminal->dls .= ',' . $post1['dls_id'];
                if ($this->admininfo()['auth_ids'] == 7) {
                    $terminal->activation_time = time();
                    $terminal->jiju_id = $post1['jiju_id'];
                    $terminal->tixian_id = $post1['tixian_id'];
                    Db::name('channel_terminal')->insert(['channel_id' => $v, 'sydls_id' => $post1['dls_id'], 'jiesuan_id' => $post1['jiesuan_id'],
                        'form_id' => $this->admininfo()['id'],
//                    'to_id'=>$post1['dls_id'],
                        'jiju_id' => $post1['jiju_id'], 'tixian_id' => $post1['tixian_id']]);
                }else{
                    //中间表添加数据
                    Db::name('channel_terminal')->insert(['channel_id' => $v, 'sydls_id' => $post1['dls_id'], 'jiesuan_id' => $post1['jiesuan_id'],
                        'form_id' => $this->admininfo()['id'],
//                    'to_id'=>$post1['dls_id'],
                        'jiju_id' => $terminal['jiju_id'], 'tixian_id' => $terminal['tixian_id']]);
                }

                //更新上级代理商关系
                Db::name('channel_terminal')->where(['channel_id' => $v, 'sydls_id' => $this->admininfo()['id']])->update(['to_id' => $post1['dls_id']]);
                $terminal->jiesuan_id = $post1['jiesuan_id'];
                $terminal->save();
            }
            Db::name('terminal_action')->insert([
                'sn' => $post1['dataid'], 'dls' => $post1['dls_id'], 'action' => '后台划拨', 'cate_id' => $terminal['cate_id'], 'create_time' => date('Y-m-d H:i', time()), 'agent_id' => $this->admininfo()['id']
            ]);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('分配失败', $e->getMessage());
        }

        echo "<script>window.parent.location.reload()</script>";
    }

    /**
     * @NodeAnotation(title="批量入库")
     */
    public function batch()
    {
        $b = Terminancate::field('brand,id')->select();
        $this->assign(['b' => $b]);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="批量入库保存")
     */
    public function batch_add()
    {
        $file = request()->file('sn');
        //文文件类型
        $mime_type = $file->getOriginalExtension();
        if ($mime_type != 'txt') {
            echo "<script>alert('请上传txt')</script>";
            echo "<script>window.parent.location.reload()</script>";
            return;
        }
        $post = $this->request->post();
        $file = file_get_contents($file);
        $file = explode("\r\n", $file);
// 启动事务

        Db::startTrans();
        try {
            $num = 0;
            foreach ($file as $k => $v) {
                $count = $this->model->where('sn', $v)->count();
                if ($count > 0) {
                    $num += 1;
                    continue;
                }
                $post['top_code'] = $this->admininfo()['appid'];
                $post['dls_id'] = $this->admininfo()['id'];
                $post['dls'] = $this->admininfo()['id'];
                $post['sn'] = $v;
                $save = $this->model->create($post);
                Db::name('channel_terminal')->insert(['channel_id' => $save['id'],
                    'sydls_id' => $this->admininfo()['id'],
                    'form_id' => $this->admininfo()['id'],
                    'jiesuan_id' => 1,
                    'jiju_id' => 1,
                    'tixian_id' => 1,
                ]);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        echo "<script>alert('导入失败.$num.条,')</script>";
        echo "<script>window.parent.location.reload()</script>";

    }

    /**
     * @NodeAnotation(title="冻结失败重新发起冻结")
     */
    public function activity()
    {
        $id = $this->request->param('id');
        try {
            $machines = $this->model->find($id);
            $agent = SystemAdmin::where('appid', $machines['top_code'])->find();
            if ($machines['activity'] == 2) {
                $freeze = new Apirequest($agent['appid'], $agent['secret_key']);

                if ($machines['merchant_code'] == '') {
                    return json([
                        'code' => '',
                        'data' => '',
                        'msg' => '机器未绑定商户',
                        'url' => '',
                        'wait' => 3,
                    ]);
                }
                if ($machines['activity_freeze_status'] == 2) {
                    return json([
                        'code' => '',
                        'data' => '',
                        'msg' => '机器已扣款成功',
                        'url' => '',
                        'wait' => 3,
                    ]);
                }
                if ($machines['send_activity'] == 2) {
                    return json([
                        'code' => '',
                        'data' => '',
                        'msg' => '冻结请求已发送成功,请勿重复冻结',
                        'url' => '',
                        'wait' => 3,
                    ]);
                }
                //冻结费用,服务费
                $freeze = $freeze->freeze($machines['merchant_code'], $machines['sn'], $machines['di_service_charge'], $machines['pos_note_template']);
                $log = new \app\admin\model\terminal\Freeze();
                $log->sn = $machines->sn;
                $log->merchant_code = $machines->merchant_code;
                $log->merchant_name = $machines->merchant_title;
                $log->type = 1;//流量费
                $log->create_time = time();
                $log->time = date('Y-m-d H:i:s');
                $log->topcode = $agent['appid'];
                if ($freeze[1]['code'] == 00) {
                    $machines->optNo = $freeze[1]['data']['optNo'];
                    $machines->save();
                    $log->status = 2;
                    $log->optNo = $freeze[1]['data']['optNo'];
                } else {
                    $machines->optNo = $freeze['1']['message'];
                    $machines->save();
                    $log->status = 1;
                    $log->optNo = $freeze['1']['message'];
                }
                $log->save();
            } else {
                return json([
                    'code' => '',
                    'data' => '',
                    'msg' => '请先设置参与活动',
                    'url' => '',
                    'wait' => 3,
                ]);
            }
        } catch (\Exception $e) {
            $this->error('执行错误');
        }
        $this->success($freeze['1']['message']);
    }

    /**
     * @NodeAnotation(title="主动发起sim冻结")
     */

    public function activity_sim()
    {
        $id = $this->request->param('id');
        try {
            $machines = $this->model->find($id);
            $agent = SystemAdmin::where('appid', $machines['top_code'])->find();

            if ($machines['activity_sim'] == 2) {

                $freeze = new Apirequest($agent['appid'], $agent['secret_key']);

                //未成功收取和已激活
                if ($machines['merchant_code'] == '') {
                    return json([
                        'code' => '',
                        'data' => '',
                        'msg' => '机器未绑定商户',
                        'url' => '',
                        'wait' => 3,
                    ]);
                }
                if ($machines['activity_freeze_status_sim'] == 2) {
                    return json([
                        'code' => '',
                        'data' => '',
                        'msg' => '机器已扣款成功',
                        'url' => '',
                        'wait' => 3,
                    ]);
                }
                if ($machines['send_sim'] == 2) {
                    return json([
                        'code' => '',
                        'data' => '',
                        'msg' => '冻结请求已发送成功,请勿重复冻结',
                        'url' => '',
                        'wait' => 3,
                    ]);
                }

                //冻结费用,服务费
                $freeze = $freeze->simfreeze($machines['merchant_code'], $machines['sn'], $machines['sim_service_charge'], $machines['sim_note_template']);
                $log = new \app\admin\model\terminal\Freeze();
                $log->sn = $machines->sn;
                $log->merchant_code = $machines->merchant_code;
                $log->merchant_name = $machines->merchant_title;
                $log->type = 2;//流量费
                $log->create_time = time();
                $log->time = date('Y-m-d H:i:s');
                $log->topcode = $agent['appid'];
                if ($freeze[1]['code'] == 00) {
                    $machines->optNo_sim = $freeze[1]['data']['optNo'];
                    $machines->send_sim = 2;
                    $machines->save();
                    $log->status = 2;
                    $log->optNo = $freeze[1]['data']['optNo'];
                } else {
                    $machines->optNo_sim = $freeze['1']['message'];
                    $machines->save();
                    $log->status = 1;
                    $log->optNo = $freeze['1']['message'];
                }
                $log->save();
            } else {
                return json([
                    'code' => '',
                    'data' => '',
                    'msg' => '请先设置参与活动',
                    'url' => '',
                    'wait' => 3,
                ]);
            }
        } catch (\Exception $e) {
            $this->error('执行错误');
        }
        $this->success($freeze['1']['message']);
    }
}