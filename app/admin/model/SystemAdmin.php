<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\model;


use app\admin\model\terminal\Channel;
use app\common\model\TimeModel;

class SystemAdmin extends TimeModel
{

    protected $deleteTime = 'delete_time';

    public function getAuthList()
    {
        $list = (new SystemAuth())
            ->where('status', 1)
            ->column('title', 'id');
        return $list;
    }


    public function higherlevel()
    {
        return $this->belongsTo(SystemAdmin::class,'higher_level_id','id');
    }

    public function children()
    {
        return $this->higherlevel()->with('children');
    }

    //终端关联
    public function terminal()
    {
        return $this->belongsToMany(Channel::class,'channel_terminal','channel_id','sydls_id');
    }


    public function form()
    {
        return $this->belongsToMany(Channel::class,'channel_terminal','channel_id','form_id');
    }
}