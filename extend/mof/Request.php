<?php

namespace mof;

use think\exception\ValidateException;
use think\Validate;

class Request extends \think\Request
{
    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;
    /**
     * 验证规则或验证类名
     * @var array|string
     */
    protected array|string $validate = [];

    /**
     * 验证消息
     * @var array
     */
    protected array $validateMessage = [];

    /**
     * 获取提交的表单数据
     * @param string $scene 数据提交场景 格式：add,edit,...
     * @param string $type 数据来源类型 格式：param,get,post,request，支持获取部分参数,点号(.)后面键名用逗号(,)分割
     * @param bool|array|string $validate 是否对数据进行验证，true为使用内置规则验证，false为不验证，array为自定义规则数组，string为验证类名
     * @return array
     */
    public function formData(string $scene = '', string $type = 'param', bool|string|array $validate = true): array
    {
        if (str_contains('.', $type)) {
            //设置了获取部分参数
            [$type, $keys] = explode('.', $type, 2);
            $keys = explode(',', $keys); //参数列表
            $data = $this->only($keys, $type);
        } else {
            $data = $this->{$type}();
        }
        if ($validate !== false) {
            $this->validate($data, $validate, $scene);
        }
        return $data;
    }

    /**
     * 设置验证
     * @param string|array $validate
     * @param array|null $message
     * @return $this
     */
    public function withValidate(string|array $validate, array $message = null): static
    {
        $this->validate = $validate;
        if (is_array($message)) {
            $this->validateMessage = $message;
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
    public function validateRules(): ?array
    {
        $result = [];
        if (!empty($this->validate)) $result['rule'] = $this->validate;
        if (!empty($this->validateMessage)) $result['message'] = $this->validateMessage;
        return $result ?: null;
    }

    /**
     * 数据验证
     * @param array $data
     * @param array|string|bool $validate
     * @param string $scene
     * @return bool
     */
    public function validate(array $data, array|string|bool $validate = true, string $scene = ''): bool
    {
        if (true === $validate) {
            $validate = $this->validateRules();
        }

        if (!$validate) {
            return true;
        }

        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate['rule']);
            $v->message($validate['message'] ?? []);
            $v->scene($scene);
        } else {
            $class = $validate;
            $v = new $class();
        }

        if (!empty($scene)) {
            $v->scene($scene);
        }

        // 是否批量验证
        if ($this->batchValidate) {
            $v->batch(true);
        }

        //验证不通过是抛出ValidationException
        return $v->failException(true)->check($data);
    }
}