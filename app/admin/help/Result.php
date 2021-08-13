<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/20
 * Time: 10:12
 */

namespace app\admin\help;


class Result {
    //success
    static public function Success($data,$msg='succuse') {
        $rs = [
            'code'=>666,
            'msg'=>$msg,
            'data'=>$data,
        ];
        return json($rs);
    }
    //error
    static public function Error($code,$msg) {
        $rs = [
            'code'=>$code,
            'msg'=>$msg,
            'data'=>"",
        ];
        return json($rs);
    }
}