<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/20
 * Time: 10:13
 */

namespace app\admin\help;


use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use ErrorException;
use Exception;
use InvalidArgumentException;
use ParseError;
//use PDOException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\ClassNotFoundException;
use think\exception\HttpResponseException;
use think\exception\RouteNotFoundException;
use TypeError;
use \app\admin\help\Result;

class MyException extends Handle
{
    public function render($request, Throwable $e): Response
    {
        //如果处于调试模式
        if (env('app_debug')){
            //return ::Error(1,$e->getMessage().$e->getTraceAsString());
            return parent::render($request, $e);
        }
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return Result::Error(422,$e->getError());
        }

        // 请求404异常 , 不返回错误页面
        if (($e instanceof ClassNotFoundException || $e instanceof RouteNotFoundException) || ($e instanceof HttpException && $e->getStatusCode() == 404)) {
            return Result::Error(404,'当前请求资源不存在，请稍后再试');
        }

        //请求500异常, 不返回错误页面
        //$e instanceof PDOException ||
        if ($e instanceof Exception ||  $e instanceof HttpException || $e instanceof InvalidArgumentException || $e instanceof ErrorException || $e instanceof ParseError || $e instanceof TypeError)  {

            $this->reportException($request, $e);
            return Result::Error(500,'系统异常，请稍后再试');
        }

        //其他错误
        $this->reportException($request, $e);
        return Result::Error(1,"应用发生错误");
    }

    //记录exception到日志
    private function reportException($request, Throwable $e):void {
        $errorStr = "url:".$request->host().$request->url()."\n";
        $errorStr .= "code:".$e->getCode()."\n";
        $errorStr .= "file:".$e->getFile()."\n";
        $errorStr .= "line:".$e->getLine()."\n";
        $errorStr .= "message:".$e->getMessage()."\n";
        $errorStr .=  $e->getTraceAsString();

        trace($errorStr, 'error');
    }

}