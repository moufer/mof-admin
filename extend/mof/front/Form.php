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
        $dialog = $this->dialogAttrs($model);
        $form = $this->formAttrs();
        $elements = $this->elements($model);
        if (isset($elements['tabs'])) {
            foreach ($elements['tabs'] as $index => $tab) {
                $this->improveElements($tab['elements']);
                $elements['tabs'][$index] = $tab;
            }
        } else {
            $this->improveElements($elements);
        }
        return [
            'dialog'   => $dialog,
            'form'     => $form,
            'elements' => $elements
        ];
    }

    protected function dialogAttrs(\mof\Model $model = null): array
    {
        return [
            'title'      => $model ? '编辑' : '新增',
            'width'      => 650,
            'top'        => '10vh',
            'lockScroll' => false,
        ];
    }

    /**
     * 表单属性
     * @param Model|null $model
     * @return array
     */
    protected function formAttrs(\mof\Model $model = null): array
    {
        return [
            'labelWidth' => 'auto',
        ];
    }

    /**
     * 表单元素
     * @param Model|null $model
     * @return array
     */
    protected function elements(\mof\Model $model = null): array
    {
        return [];
    }

    protected function improveElements(array &$elements): void
    {
        //添加未设置的排序字段
        $sort = false;
        foreach ($elements as $index => $item) {
            if (!isset($item['order'])) {
                $item['order'] = $index + 1;
            } else {
                $sort = true;
            }
            //元素里的rules提取出来，放到form里
            if (isset($item['rules'])) {
                $item = $this->improveRules($item);
                if (!empty($item['rules'])) {
                    $form['rules'][$item['prop']] = $item['rules'];
                }
                unset($item['rules']);
            }
            $elements[$index] = $item;
        }
        //排序
        if ($sort) usort($elements, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
    }

    /**
     * 完善表单元素验证规则
     * @param array $element
     * @return array
     */
    protected function improveRules(array $element): array
    {
        foreach ($element['rules'] as $k => $rule) {
            //补充message内容
            if (empty($rule['message'])) {
                if (isset($rule['required']) && $rule['required'] === true) {
                    $rule['message'] = "{$element['label']}不能为空";
                } else {
                    $rule['message'] = "{$element['label']}格式不正确";
                }
                $element['rules'][$k] = $rule;
            }
        }
        return $element;
    }
}