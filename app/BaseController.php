<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

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
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
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

    public function getTranCode($code = '020000', $cardType = '1')
    {
        switch ($code) {
            case '020000':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS信用卡刷卡消费', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS借记卡刷卡消费', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '020002':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡消费撤销', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡消费撤销', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '020003':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡消费冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡消费冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '020023':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡消费撤销冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡消费撤销冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case 'U20000':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡电子现金', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡电子现金', 'ratetype' => 'dFeeRate'];
                }
                break;
            case 'T20003':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡日结消费冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡日结消费冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case 'T20000':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡日结消费', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡日结消费', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024100':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024102':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成撤销', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成撤销', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024103':

                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '024123':
                if ($cardType == 1) {
                    $field = ['describe' => 'POS刷卡预授权完成撤销 冲正', 'ratetype' => 'cFeeRate'];
                } else {
                    $field = ['describe' => 'POS刷卡预授权完成撤销 冲正', 'ratetype' => 'dFeeRate'];
                }
                break;
            case '02B100':
                $field = ['describe' => '扫码交易支付宝被扫', 'ratetype' => 'alipayFeeRate'];
                break;
            case '02B200':
                $field = ['describe' => '扫码交易支付宝主扫', 'ratetype' => 'alipayFeeRate'];
                break;
            case '02W100':
                $field = ['describe' => '扫码交易微信被扫', 'ratetype' => 'wechatPayFeeRate'];
                break;
            case '02W200':
                $field = ['describe' => '扫码交易微信主扫', 'ratetype' => 'wechatPayFeeRate'];
                break;
            case '02Y100':
                $field = ['describe' => '扫码交易银联被扫', 'ratetype' => 'ycFreeFeeRate'];
                break;
            case '02Y200':
                $field = ['describe' => '扫码交易银联主扫', 'ratetype' => 'ycFreeFeeRate'];
                break;
            case '02Y600':
                $field = ['describe' => '扫码交易银联二维码撤销', 'ratetype' => 'ycFreeFeeRate'];
                break;
            case '0AY100':
                if ($cardType == 1) {
                    $field = ['describe' => 'APP交易APP银联被扫', 'ratetype' => 'ycFreeFeeRate '];
                } else {
                    $field = ['describe' => 'APP交易APP银联被扫', 'ratetype' => 'ydFreeFeeRate '];
                }
                break;
            case '0AY200':
                if ($cardType == 1) {
                    $field = ['describe' => 'APP交易APP银联主扫', 'ratetype' => 'ycFreeFeeRate '];
                } else {
                    $field = ['describe' => 'APP交易APP银联主扫', 'ratetype' => 'ydFreeFeeRate '];
                }
                break;
            default:
                $field = ['describe' => 'POS刷卡消费', 'ratetype' => 'cFeeRate'];
                break;
        }

        return $field;
    }



}
