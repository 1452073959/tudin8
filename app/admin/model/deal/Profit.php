<?php

namespace app\admin\model\deal;

use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;
use app\common\model\TimeModel;

class Profit extends TimeModel
{

    protected $name = "deal_profit";

    protected $deleteTime = false;

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    //机具/商户
    public function terminal()
    {
        return $this->belongsTo(Channel::class, 'terminal_id', 'id');
    }


    //代理商
    public function dls()
    {
        return $this->belongsTo(SystemAdmin::class, 'agent_id', 'id');
    }
    //流水
    public function deal()
    {
        return $this->belongsTo(Deallist::class,'deal_id','id');
    }
}