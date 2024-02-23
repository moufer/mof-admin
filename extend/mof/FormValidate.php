<?php

namespace mof;

class FormValidate
{
    protected Request $request;
    /**
     * 提交参数集合
     * 格式 ["字段名/修饰符,过滤标识"]
     * @var array
     */
    protected array $param = [];

    /**
     * 根据场景获接收的参数
     * 格式 [场景名 => "字段名1,字段名2"]
     * @var array
     */
    protected array $allow = [];

    /**
     * 默认值
     * @var array
     */
    protected array $default = [];

    /**
     * 固定值
     * @var array
     */
    protected array $fixed = [];

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batch = false;

    /**
     * 验证规则或验证类名
     * @var array|string
     */
    protected array|string $rule = [];

    /**
     * 验证消息
     * @var array
     */
    protected array $message = [];

    /**
     * 根据场景选择验证规则
     * @var array
     */
    protected array $sceneOnly = [];

    /**
     * 当前场景值
     * @var string
     */
    protected string $scene = '';

    public function __construct()
    {
        $this->request = app(Request::class);
    }

    public static function make($option = []): static
    {
        $instance = new static();
        $instance->option($option);
        return $instance;
    }

    public function option($option): void
    {
        $keys = [
            'param', 'allow', 'batch', 'scene',
        ];
        foreach ($keys as $key) {
            $method = 'with' . ucfirst($key);
            if (isset($option[$key])) {
                $this->$method($option[$key]);
            }
        }
        if (!empty($option['only'])) {
            $this->withSceneOnly($option['only']);
        }
        if (!empty($option['rule'])) {
            $validate = [
                'rule'    => $option['rule'],
                'message' => $option['message'] ?? null,
            ];
            $this->withValidate($validate);
        }
    }

    /**
     * 获取提交的表单数据
     * @param string $type 数据来源类型 格式：param,get,post,...，支持获取部分参数,点号(.)后面键名用逗号(,)分割
     * @param bool $validate 是否对数据进行验证，true为使用内置规则验证
     * @return array
     */
    public function get(string $type = 'param', bool $validate = true): array
    {
        //允许接收的字段
        $allowParams = array_keys($this->param ?: $this->request->{$type}());

        if (str_contains('.', $type)) {
            //设置了获取部分参数，如 param.username,password
            [$type, $keys] = explode('.', $type, 2);
            $allowParams = explode(',', $keys); //指定了获取内容
        } else {
            //没有指定就从类定义获取
            if ($this->allow) {
                if ($this->scene && !empty(($this->allow[$this->scene]))) {
                    $allowParams = explode(',', $this->allow[$this->scene]); //转为字段数组
                }
            }
        }

        //获取表单内容
        $data = $this->getFormData($allowParams, $type);

        //默认值设置
        foreach ($this->default as $key => $value) {
            if (!isset($data[$key])) $data[$key] = $value;
        }

        //合并固定值设置
        $data = array_merge($data, $this->fixed);

        //处理验证
        if ($validate === true) {
            if ($validate = $this->getValidate()) {
                if ($this->scene && !empty($this->sceneOnly[$this->scene])) {
                    //配置了场景验证字段
                    $allowParams = explode(',', $this->sceneOnly[$this->scene]);
                } else if ($allowParams) {
                    //只验证允许提交的字段
                    $allowParams = array_map(function ($item) {
                        //去除item里，"/"以及后面的字符
                        $i = strpos($item, '/');
                        return $i ? substr($item, 0, $i) : $item;
                    }, $allowParams);
                }

                //如果$validate是一个自定义的验证类，且类内存在场景时，only操作无效，
                //具体验证规则由，验证类里的场景规则来决定
                $validate->only($allowParams);
                !empty($allowParams) && $validate->only($allowParams);

                //验证不通过报 ValidationException
                $validate->failException(true)->check($data);
            }
        }

        return $data;
    }

    /**
     * 设置允许写入数据库的字段
     * 格式 [字段名 => 字段类型(int,string,array,html,float,boolean)]
     * @param array $param
     * @param bool $override
     * @return $this
     */
    public function withParam(array $param, bool $override = true): static
    {
        //格式化参数
        //['key/symbol,filter'] => ['key'=>['symbol','filter']]
        $format = [];
        foreach ($param as $index => $item) {
            if (str_contains($item, '/')) {
                [$key, $symbol] = explode('/', $item, 2);
                if (str_contains($symbol, ',')) {
                    $value = explode(',', $symbol, 2);
                    $value[1] = $this->getFilter($value[1]);
                } else {
                    $value = [$symbol, ''];
                }
            } else {
                $key = $item;
                $value = ['s', ''];
            }
            $format[$key] = $value;
        }
        $this->param = array_merge($override ? [] : $this->param, $format);
        return $this;
    }

    /**
     * 设置允许接收的字段
     * 获取表单内容时，只获取允许接收的字段
     * @param string|array $param
     * @param string $scene
     * @return $this
     */
    public function withAllow(string|array $param, string $scene = ''): static
    {
        if (is_array($param)) {
            $this->allow = $param;
        } else if ($scene) {
            $this->allow[$scene] = $scene;
        }

        return $this;
    }

    /**
     * 设置默认值
     * 获取表单数据时，会合并固定值，参与验证
     * @param array $data
     * @return $this
     */
    public function withDefault(array $data = []): static
    {
        $this->default = $data;
        return $this;
    }

    /**
     * 设置固定值
     * 获取表单数据时，会合并固定值，参与验证
     * 在执行获取表单数据后，会清空固定值
     * @param array $data
     * @return $this
     */
    public function withFixed(array $data = []): static
    {
        $this->fixed = $data;
        return $this;
    }

    /**
     * 设置场景对于验证参数
     * @param string|array $param
     * @param string $scene
     * @return $this
     */
    public function withSceneOnly(string|array $param, string $scene = ''): static
    {
        if (is_array($param)) {
            $this->sceneOnly = $param;
        } else if ($scene) {
            $this->sceneOnly[$scene] = $scene;
        }

        return $this;
    }

    /**
     * 设置当前场景
     * @param string $scene
     * @return $this
     */
    public function withScene(string $scene): static
    {
        $this->scene = $scene;
        return $this;
    }

    /**
     * 设置验证
     * @param array $validate 验证规则 格式：[rule=>rule,message=>message]
     * @param bool $override 是否覆盖
     * @return $this
     */
    public function withValidate(array $validate, bool $override = true): static
    {
        if (is_string($validate['rule'])) {
            $this->rule = $validate['rule'];
        } else {
            if (is_string($this->rule)) {
                $this->rule = [];
            }
            $this->rule = array_merge($override ? [] : $this->rule, $validate['rule']);
        }
        if (!empty($validate['message'])) {
            $this->message = array_merge($override ? [] : $this->message, $validate['message']);
        }

        //判断param是否为空时，用规则里的字段作为param
//        if (empty($this->param)) {
//            $keys = $this->getValidate()->getRuleKeys();
//            $this->withParam($keys);
//        }

        return $this;
    }

    /**
     * 允许批量验证
     * @param bool $batch
     * @return $this
     */
    public function withBatch(bool $batch = true): static
    {
        $this->batch = $batch;
        return $this;
    }

    /**
     * 数据验证
     * @param array $data 验证数据
     * @param array|string $validate 验证规则 格式：[rule=>rule,message=>message] | ValidateClass
     * @param string $scene 场景
     * @param bool $batch 批量验证
     * @return bool
     */
    public function validate(array $data, array|string $validate, string $scene = '', bool $batch = false): bool
    {
        if (!$validate) {
            return true;
        }

        if (is_array($validate)) {
            $v = new \mof\Validate();
            $v->rule($validate['rule']);
            $v->message($validate['message'] ?? []);
        } else {
            $v = new $validate(); //实例化验证类
            if ($scene && $v->hasScene($this->scene)) {
                $v->scene($scene);
            }
        }

        if ($batch) {
            $v->batch(true);
        }

        //验证不通过是抛出ValidationException
        return $v->failException(true)->check($data);
    }


    /**
     * 获取验证规则
     * @return ?Validate 格式：[rule=>rule,message=>message]
     */
    protected function getValidate(): ?Validate
    {
        $validate = null;
        if (!empty($this->rule)) {
            $rules = $this->rule;
            if (is_string($rules)) {
                /** @var Validate $validate */
                $validate = new $rules();
            } else {
                $validate = new Validate();
                $validate->rule($rules);
            }
            if (!empty($this->message)) {
                $validate->message($this->message);
            }
            if ($this->scene && $validate->hasScene($this->scene)) {
                $validate->scene($this->scene);
            }
        }
        return $validate;
    }

    /**
     * 获取表单数据
     * @param $keys
     * @param $type
     * @return array
     */
    protected function getFormData($keys, $type): array
    {
        $result = [];
        foreach ($keys as $key) {

            //如果没有定义可接受参数，则默认以字符串形式全部接收
            $valType = !empty($this->param) ? ($this->param[$key] ?? false) : ['s', ''];
            if (false === $valType) continue;                //未定义的字段
            if (!$this->request->has($key, $type)) continue; //未赋值的字段

            //获取过滤后的数据
            $result[$key] = $this->request->{$type}("{$key}/{$valType[0]}", null, $valType[1] ?? '');
        }
        return $result;
    }

    /**
     * 根据类型获取过滤器
     * @param $flag
     * @return string|array
     */
    protected function getFilter($flag): string|array
    {
        return match ($flag) {
            'html' => '\mof\facade\HtmlSecurity::xss_clean', //保留html特性去除xss
            default => $flag,
        };
    }
}