<?php

namespace app\admin\model\terminal;

use app\admin\model\SystemAdmin;
use app\common\model\TimeModel;

class Terminalreturn extends TimeModel
{

    protected $name = "terminal_return";

    protected $deleteTime = false;

    //回拨申请人
    public function form()
    {
        return $this->belongsTo(SystemAdmin::class,'form_id','id');
    }
    //回拨的上级
    public function to()
    {
        return $this->belongsTo(SystemAdmin::class,'to_id','id');
    }
    

}