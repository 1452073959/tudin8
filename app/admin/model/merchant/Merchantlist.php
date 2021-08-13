<?php

namespace app\admin\model\merchant;

use app\admin\help\Apirequest;
use app\common\model\TimeModel;
use app\admin\model\SystemAdmin;
use app\admin\model\terminal\Channel;

class Merchantlist extends TimeModel
{

    protected $name = "merchant_merchantlist";

    protected $deleteTime = false;


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