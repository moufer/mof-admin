<?php

declare (strict_types=1);

namespace mof;

use mof\annotation\Inject;
use think\App;

abstract class BaseController
{
    /**
     * 应用实例
     * @var App
     */
    protected App $app;

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        //解析注入
        $this->inject();

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 解析#[inject]注解，实例化注入类
     * @return void
     */
    protected function inject(): void
    {
        $reflect = new \ReflectionObject($this);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            if ($getAttribute = ($property->getAttributes()[0] ?? false)) {
                if ('mof\annotation\Inject' === $getAttribute->getName()) {
                    $this->{$property->name} = Inject::make($getAttribute, $property);
                }
            }
        }
    }

}