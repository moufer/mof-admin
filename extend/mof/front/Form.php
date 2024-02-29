<?php

namespace mof\front;

use Exception;
use mof\FormValidate;
use mof\Model;
use mof\Request;

/**
 * @mixin FormValidate
 */
class Form
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var FormValidate
     */
    protected FormValidate $formValidate;

    /**
     * 验证信息
     * 格式 [params=>params,allow=>allow,only=>only,rule=>rule,message=>message]
     */
    protected array $validate = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->formValidate = FormValidate::make($this->validate);
        $this->initialize();
    }

    /**
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        //检测$name是不是$formValidate里的方法，如果是则调用$formValidate里的方法
        if (method_exists($this->formValidate, $name)) {
            return call_user_func_array([$this->formValidate, $name], $arguments);
        } else {
            //如果不是则抛出异常
            throw new Exception('The method does not exist: ' . $name);
        }
    }

    /**
     * 初始化
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * 生成表单
     * @param Model|null $model
     * @return array
     */
    public function build(Model $model = null): array
    {
        $result = $this->elements($model);
        //添加未设置的排序字段
        $sort = false;
        foreach ($result as $index => $item) {
            if (!isset($item['order'])) {
                $item['order'] = $index + 1;
            } else {
                $sort = true;
            }
            $result[$index] = $item;
        }
        //排序
        if ($sort) usort($result, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        return $result;
    }

    /**
     * 表单元素
     * @param Model|null $model
     * @return array
     */
    protected function elements(\mof\Model $model = null): array
    {
        return [
        ];
    }
}