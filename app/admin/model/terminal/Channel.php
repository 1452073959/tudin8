<?php

namespace app\admin\model\terminal;

use app\admin\model\deal\Profit;
use app\admin\model\merchant\Merchantlist;
use app\admin\model\SystemAdmin;
use app\common\model\TimeModel;
use app\admin\model\template\Jiju;
use app\admin\model\template\Jiesuan;
use app\admin\model\template\Tixian;
class Channel extends TimeModel
{

    protected $name = "terminal_channel";

//    protected $deleteTime = "delete_time";

//    protected $type = [
//        'retuen_reach_time'=>'timestamp',
//        'return_activate_time'=>'timestamp',
//        'commercial_time'=>'timestamp',
//        'reach_time'=>'timestamp',
//        'activation_time'=>'timestamp',
//    ];

    public function getStatusList()
    {
        return ['0'=>'入库','1'=>'未激活','2'=>'已激活未达标','3'=>'已激活未达标','4'=>'已达标','5'=>'超期未达标','6'=>'超期未激活','7'=>'不参与活动已激活'];
    }


    //结算模板
    public function jiesuan()
    {
        return $this->belongsTo(Jiesuan::class, 'jiesuan_id', 'id');
    }
    //机具模板
    public function jiju()
    {
        return $this->belongsTo(Jiju::class, 'jiju_id', 'id');
    }
    //提现模板
    public function tixian()
    {
        return $this->belongsTo(Tixian::class, 'tixian_id', 'id');
    }


    //代理商
    public function dls()
    {
        return $this->belongsTo(SystemAdmin::class, 'dls_id', 'id');
    }
    //商户,一对一
    public function merchant()
    {
        return $this->hasOne(Merchantlist::class,'terminal_id','id');
    }

    //belongsToMany('关联模型名','中间表名','外键名','当前模型关联键名',['模型别名定义']);

    public function js()
    {
        return $this->belongsToMany(Jiesuan::class,'channel_terminal','','jiesuan_id');
    }
    public function js1()
    {
        return $this->belongsToMany(Jiesuan::class,'channel_terminal','jiesuan_id','channel_id');
    }
    public function profit()
    {
        return $this->hasMany(Profit::class,'terminal_id','id');
    }

    public function cate()
    {
        return $this->belongsTo(Terminancate::class,'cate_id','id');
    }


}