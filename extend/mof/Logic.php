<?php

namespace mof;

use mof\annotation\Inject;
use mof\concern\logic\Curd;
use think\App;

class Logic
{
    use Curd;

    /**
     * @var Model|null 操作模型
     */
    protected $model = null;

    /**
     * 模型是否已初始化
     * @var bool
     */
    protected bool $modelIsInitialized = false;

    public static function make(Model $model): static
    {
        $instance = new static(app());
        $instance->model($model);
        return $instance;
    }

    public function __construct(protected App $app)
    {
        $this->inject();
        $this->autoInstanceModel();
        $this->initialize();
    }

    public function initialize()
    {
    }

    /**
     * 获取模型
     * @param null $model
     * @return Model
     */
    public function model($model = null): Model
    {
        if ($model instanceof Model) {
            $this->model = $model;
        }
        return $this->model;
    }

    /**
     * 自动实例化逻辑类对应的模型类
     * @return void
     */
    protected function autoInstanceModel(): void
    {
        if (!$this->modelIsInitialized) {
            $pathInfo = explode('\\', get_class($this));
            $className = rtrim(array_pop($pathInfo), 'Logic'); //类名
            //去掉上级目录
            if ('logic' !== array_pop($pathInfo)) {
                return;
            }
            $namespace = implode('\\', $pathInfo) . "\\model"; //命名空间
            $modelName = $namespace . '\\' . $className;
            if (class_exists($modelName)) {
                $this->model = new $modelName;
                $this->modelIsInitialized = true;
            }
        }
    }

    /**
     * 解析#[injectModel(class)]注解，自动注入类
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
                    if ('model' === $property->name) {
                        $this->modelIsInitialized = true;
                    }
                }
            }
        }
    }
}