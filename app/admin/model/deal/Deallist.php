<?php

namespace app\admin\model\deal;

use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;
use app\common\model\TimeModel;

class Deallist extends TimeModel
{

    protected $name = "deal_deallist";

    protected $deleteTime = "delete_time";

    // 定义时间戳字段名
    protected $createTime = 'deal_create_time';
    //代理商
    public function dls()
    {
        return $this->belongsTo(SystemAdmin::class, 'dls_id', 'id');
    }
    //机具
    public function terminal()
    {
        return $this->belongsTo(Channel::class, 'terminal_id', 'id');
    }

}