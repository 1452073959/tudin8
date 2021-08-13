<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use app\admin\model\SystemAdmin;
use app\admin\help\Result;

/**
 * 控制器基础类
 */
abstract class ApiController
{

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
//    public function __construct(App $app)
//    {
//        // 控制器初始化
//        $this->initialize();
//    }
    public function __construct()
    {
//        $this->checkToken();
    }
    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }


    public function user($request)
    {
        $token = $request->header('token');
        $user = SystemAdmin::where('token', $token)->find();
        return $user;
    }
    //获取一个用户的最顶级
    public static function merchants_sub($higher_level_id, $array = [])
    {
        $merchants_users = \app\admin\model\SystemAdmin::where('id', $higher_level_id)->find()->toArray();
//        dump($merchants_users);die;
        $array[] = $merchants_users;
        if ($merchants_users['higher_level_id']) {
            return self::merchants_sub($merchants_users['higher_level_id'], $array);
        }

        return $array;

    }

    //品牌
    public function brand($request)
    {
        $brand=  $request->header('brand');
        return $brand;
    }





}
