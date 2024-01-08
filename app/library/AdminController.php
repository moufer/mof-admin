<?php

namespace app\library;

use app\concern\Batch;
use mof\ApiController;
use mof\ApiResponse;
use mof\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\Paginator;
use think\response\Json;

/**
 * 后台管理基础控制器
 * Class AdminController
 * @package app\library
 * @method  onSelectAfter(Paginator $list) 选择列表查询后回调
 * @method  onReadAfter(Model $model) 详情查询后回调
 * @method  onSaveAfter(Model $model) 添加后回调
 * @method  onUpdateAfter(Model $model) 编辑后回调
 * @method  onDeleteAfter(Model|Model[] $model) 删除后回调
 */
class AdminController extends ApiController
{
    use Batch;

    /** @var string $modelName 模型名称 */
    protected string $modelName = '';

    /** @var ?Model $model 模型 */
    protected ?Model $model = null;

    /** @var string $validateName 验证器名称 */
    protected string $validateName = '';

    /** @var array $validateRules 验证规则 */
    protected array $validateRules = [];

    /** @var array $validateMessage 验证提示 */
    protected array $validateMessage = [];

    /** @var array $readAppend 读取追加字段 */
    protected array $readAppend = [];

    /** @var bool $softDelete 是否开启软删除 */
    protected bool $softDelete = false;

    /** @var \mof\Searcher 搜索器 */
    protected \mof\Searcher $searcher;

    protected function initialize(): void
    {
        parent::initialize();
        if ($this->modelName) {
            $this->model = new $this->modelName;
//            $reflection = new ReflectionClass(self::class);
//            $traits = $reflection->getTraitNames();
//            foreach ($traits as $trait) {
//                //打开软删除开关
//                str_contains($trait, 'SoftDelete') && $this->softDelete = true;
//            }
        }
    }

    /**
     * 读取详情
     * @param $id
     * @return Json
     * @throws DbException
     */
    public function read($id): Json
    {
        $id = $this->request->param('id/d', 0);
        if (!$model = $this->model->find($id)) {
            return ApiResponse::error('[read]数据不存在');
        }
        //读取追加字段
        if ($this->readAppend) {
            $model->append($this->readAppend);
        }
        $this->model = $model;
        //读取后操作
        if (method_exists($this, 'onReadAfter')) {
            call_user_func([$this, 'onReadAfter'], $this->model);
        }
        return ApiResponse::success($this->model);
    }

    /**
     * 添加
     * @return Json
     * @throws \Exception
     */
    public function save(): Json
    {
        //判断是不是put请求
        if (!$this->request->isPost()) {
            return ApiResponse::error('请求方式错误');
        }
        //获取参数
        $data = $this->request->param();
        if (method_exists($this, 'onSaveBefore')) {
            $data = call_user_func([$this, 'onSaveBefore'], $data);
        }
        //验证
        try {
            if ($this->validateName) {
                $this->validate($data, $this->validateName . '.add');
            } else if ($this->validateRules) {
                $this->validate($data, $this->validateRules, $this->validateMessage);
            }
        } catch (ValidateException $exception) {
            return ApiResponse::fail($exception->getMessage());
        }

        //添加
        $this->model->data($data, true);
        $this->model->save();
        //读取后操作
        if (method_exists($this, 'onSaveAfter')) {
            call_user_func([$this, 'onSaveAfter'], $this->model);
        }
        return ApiResponse::success($this->model);
    }

    /**
     * 编辑
     * @param $id
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function update($id): Json
    {
        //判断是不是put请求
        if (!$this->request->isPut()) {
            return ApiResponse::error('请求方式错误');
        }
        $id = $this->request->param('id/d', 0);
        //获取数据
        if (!$model = $this->model->find($id)) {
            return ApiResponse::error('数据不存在');
        }
        //获取参数
        $data = $this->request->param();
        if (method_exists($this, 'onUpdateBefore')) {
            $data = call_user_func([$this, 'onUpdateBefore'], $model, $data);
        }
        //验证
        try {
            if ($this->validateName) {
                $this->validate($data + ['id' => $id], $this->validateName . '.edit');
            } else if ($this->validateRules) {
                $this->validate($data + ['id' => $id], $this->validateRules, $this->validateMessage);
            }
        } catch (ValidateException $exception) {
            return ApiResponse::fail($exception->getMessage());
        }

        //编辑
        $model->data($data, true)->save();
        //编辑后操作
        if (method_exists($this, 'onUpdateAfter')) {
            $model = call_user_func([$this, 'onUpdateAfter'], $model);
        }
        $this->model = $model;
        return ApiResponse::success($this->model);
    }

    /**
     * 删除
     * @param $id
     * @return Json
     * @throws DbException
     */
    public function delete($id): Json
    {
        //判断是不是delete请求
        if (!$this->request->isDelete()) {
            return ApiResponse::error('请求方式错误');
        }
        if (!$model = $this->model->find($id)) {
            return ApiResponse::error('数据不存在');
        }
        //删除前
        if (method_exists($this, 'onDeleteBefore')) {
            call_user_func([$this, 'onDeleteBefore'], $model);
        }
        $model->delete();
        $this->model = $model;
        //删除后操作
        if (method_exists($this, 'onDeleteAfter')) {
            call_user_func([$this, 'onDeleteAfter'], $model);
        }
        return ApiResponse::success($model);
    }

}
