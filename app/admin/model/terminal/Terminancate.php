<?php

namespace app\admin\model\terminal;

use app\admin\model\SystemAdmin;
use app\common\model\TimeModel;

class Terminancate extends TimeModel
{

    protected $name = "terminal_cate";

    protected $deleteTime = false;

    //机具
    public function channel()
    {
        return $this->hasMany(Channel::class,'cate_id','id');
    }

    //代理商
    public function admin()
    {
        return $this->belongsTo(SystemAdmin::class,'agent_id','id');
    }
}