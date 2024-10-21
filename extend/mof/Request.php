<?php

namespace mof;

use think\Validate;

/**
 * 请求类
 * 增加验证验证功能(支持验证的方法：param,get,post,put,request,route,only,except)
 * 举例 $request->withValidate($rule)->param()
 * @package mof
 */
class Request extends \think\Request
{
    /**
     * 全局过滤
     * 过滤两遍空格，过滤html标签，转码特殊字符
     * @var string[]
     */
    protected $filter = ['trim', 'strip_tags', 'htmlspecialchars'];

    /**
     *  是否已完成一次验证（get,put,..等操作时验证）
     * @var bool
     */
    protected bool $validated = false;

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 验证规则或验证类名
     * @var array|string|\mof\Validate
     */
    protected array|string|\mof\Validate $validate = [];

    /**
     * 验证消息
     * @var array
     */
    protected array $validateMessage = [];

    /**
     * 场景值
     * @var string
     */
    protected string $scene = '';

    /**
     * @inheritdoc
     */
    public function param($name = '', $default = null, array|string|null $filter = '')
    {
        $data = parent::param($name, $default, $filter);
        if ($this->validate) $this->validate($name && is_string($name) ? [$name => $data] : $data);
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function get(bool|array|string $name = '', $default = null, array|string|null $filter = '')
    {
        $data = parent::get($name, $default, $filter);
        if ($this->validate) $this->validate($name && is_string($name) ? [$name => $data] : $data);
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function post(bool|array|string $name = '', $default = null, array|string|null $filter = '')
    {
        $data = parent::post($name, $default, $filter);
        if ($this->validate) $this->validate($name && is_string($name) ? [$name => $data] : $data);
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function put(bool|array|string $name = '', $default = null, array|string|null $filter = '')
    {
        $data = parent::put($name, $default, $filter);
        if ($this->validate) $this->validate($name && is_string($name) ? [$name => $data] : $data);
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function request(bool|array|string $name = '', $default = null, array|string|null $filter = '')
    {
        $data = parent::request($name, $default, $filter);
        if ($this->validate) $this->validate($name && is_string($name) ? [$name => $data] : $data);
        return $data;
    }

    /**
     * 设置验证
     * @param string|array|Validate $validate
     * @param array|null $message
     * @param null $scene
     * @param bool $batch
     * @return $this
     */
    public function withValidate(string|array|\mof\Validate $validate, array $message = null, $scene = null, bool $batch = false): static
    {
        if (is_array($validate)) {
            $_validate = $validate;
            $validate = [];
            foreach ($_validate as $key => $item) {
                //如果没有key，只有item，则item就是key，item=require
                if (is_numeric($key)) {
                    $validate[$item] = 'require';
                } else {
                    $validate[$key] = $item;
                }
            }
        }
        $this->validate = $validate;
        if (is_array($message)) {
            $this->validateMessage = $message;
        }
        $this->batchValidate = $batch;
        if ($scene !== null) {
            $this->scene = $scene;
        }
        return $this;
    }

    /**
     * 允许批量验证
     * @param bool $batch
     * @return $this
     */
    public function withBatchValidate(bool $batch = true): static
    {
        $this->batchValidate = $batch;
        return $this;
    }

    /**
     * 获取验证规则
     * @return array|null 格式：[rule=>rule,message=>message]
     */
    public function validateRules(): mixed
    {
        $result = [];
        if (!empty($this->validate)) {
            if (is_object($this->validate) || is_string($this->validate)) {
                return $this->validate;
            }
            $result['rule'] = $this->validate;
            if (!empty($this->validateMessage)) $result['message'] = $this->validateMessage;
        }
        return $result ?: null;
    }

    /**
     * 数据验证
     * @param mixed $data
     * @return mixed
     */
    public function validate(array $data): mixed
    {
        $validate = $this->validateRules();

        if (!$validate) {
            return $data;
        }

        if (is_array($validate)) {
            $v = new \mof\Validate();
            $v->rule($validate['rule']);
            $v->message($validate['message'] ?? []);
        } else if ($validate instanceof \mof\Validate) {
            $v = $validate;
        } else {
            $v = new $validate();
        }

        $scene = $this->scene;
        if (!empty($scene) && $v->hasScene($scene)) {
            $v->scene($scene);
        }

        // 是否批量验证
        if ($this->batchValidate) {
            $v->batch(true);
        }

        //清除
        $this->validate = [];
        $this->validateMessage = [];
        $this->scene = '';

        $this->validated = true;

        //验证不通过是抛出ValidationException
        return $v->failException(true)->check($data);
    }

}