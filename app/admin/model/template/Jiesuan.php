<?php

namespace app\admin\model\template;
use app\admin\model\SystemAdmin;
use app\common\model\TimeModel;

class Jiesuan extends TimeModel
{

    protected $name = "template_jiesuan";

    protected $deleteTime = false;

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    //关联录入人
    public function write()
    {
        return $this->belongsTo(SystemAdmin::class, 'agent_id', 'id');
    }



}