<?php

declare (strict_types=1);

namespace mof;

use think\App;
use think\exception\ValidateException;
use think\Request;
use think\Validate;

abstract class BaseController
{
    /**
     * Request实例
     * @var Request
     */
    protected Request $request;

    /**
     * 应用实例
     * @var App
     */
    protected App $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected array $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        //解析注入
        $this->injectLogic();

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 验证数据
     * @access protected
     * @param array|string $data 数据
     * @param array|string $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array
     * @throws ValidateException
     */
    protected function validate(array|string $data, array|string $validate, array $message = [], bool $batch = false): array
    {
        if (is_string($data)) {
            if ('.' !== $data[0]) {
                throw new ValidateException('数据来源无效');
            }
            $funName = substr($data, 1); //数据来源，post,get,param,request
            $data = $this->request->{$funName}();
        }
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\')
                ? $validate
                : $this->app->parseClass('validate', $validate);

            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        $v->failException(true)->check($data);

        return $data;
    }

    /**
     * 解析#[inject]注解，实例化注入类
     * @return void
     */
    protected function injectLogic(): void
    {
        $reflect = new \ReflectionObject($this);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            $getAttributes = $property->getAttributes();
            if (count($getAttributes) > 0) {
                if ('mof\annotation\Inject' === $getAttributes[0]->getName()) {
                    $propertyName = $property->name;
                    $propertyType = $property->getType()->getName();
                    $this->$propertyName = $this->app->make($propertyType); //注入app
                }
            }
        }
    }

}