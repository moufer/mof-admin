<?php

namespace mof\front;

use JetBrains\PhpStorm\ArrayShape;
use think\helper\Str;

/**
 * 前端数据管理表格配置
 */
abstract class Table
{
    /** @var string 表名 */
    protected string $table = '';
    /** @var string 接口基础地址 */
    protected string $serverBaseUrl = '/{module}/{table}';
    /** @var array|string[] 接口可接受的行为 */
    protected array $serverActions = [
        'search'  => '',
        'create'  => '',
        'add'     => '/create',
        'edit'    => '/{id}/edit',
        'read'    => '/{id}',
        'update'  => '/{id}',
        'delete'  => '/{id}',
        'updates' => '/updates',
        'deletes' => '/deletes',
        'status'  => '/status'
    ];
    /** @var array 表格选项 */
    protected array  $tabs      = [];
    protected string $tabProp   = '';
    protected string $activeTab = '';

    /** @var array|string[] 操作条按钮 */
    protected array $toolbarButtons = ['add', 'delete', 'status', 'refresh', 'search'];

    /** @var string 主键名称 */
    protected string $pk = 'id';
    /** @var bool 允许多选 */
    protected bool $tableSelection = true;
    /** @var array 表格列 */
    protected array $tableColumns = [];
    /** @var bool 使用树形表格 */
    protected bool $useTreeTable = false;

    /** @var bool 是否显示搜索 */
    protected bool $showSearch = true;
    /** @var array 搜索项 */
    protected array $searchItems = [];

    /** @var array 数据表单增改项 */
    protected array $formItems = [];

    /** @var string 排序字段 */
    protected string $sortField = 'id';
    /** @var string 排序方式 */
    protected string $sort = 'desc';

    /** @var bool 是否显示分页 */
    protected bool $showPagination = true;
    /** @var array|int[] 分页大小选项 */
    protected array $pageSizes = [10, 20, 50, 100];
    /** @var int 分页大小 */
    protected int $pageSize = 10;

    /** @var array|array[] 列默认选项 */
    protected array $defaultOptions = [
        'normal'        => ['width' => 100, 'align' => 'center'],
        'prop.id'       => ['width' => 80, 'align' => 'center'],
        'prop.sort'     => ['width' => 80, 'align' => 'center'],
        'type.datetime' => ['width' => 115, 'align' => 'center'],
        'type.icon'     => ['width' => 80, 'align' => 'center'],
        'type.media'    => ['width' => 120, 'align' => 'center'],
    ];

    /** @var array 组件配置 */
    protected array $manageOptions = [];

    /**
     * @var array Query参数数组
     */
    protected array $params = [];

    public function __construct($params = [])
    {
        $this->params = $params;
        $this->init();
    }

    protected function init(): void
    {
        //从类名里获取module和table，格式：module|app\{module}\table\{table}Table
        $class = trim(get_class($this), '\\');
        $class = explode('\\', $class);
        $this->table = Str::snake(substr(array_pop($class), 0, -5));
        //替换接口基础地址里的{module}和{table}
        $this->serverBaseUrl = str_replace(
            ['{module}', '{table}'],
            ['app' === $class[0] ? 'system' : $class[1], $this->table],
            $this->serverBaseUrl
        );
    }

    /**
     * 找类里方法名的前缀是column的方法，调用它们，把返回值赋值给tableColumns
     */
    protected function getTableColumns(): void
    {
        $result = [];
        $methods = get_class_methods($this);
        $index = 0;
        foreach ($methods as $method) {
            if (str_starts_with($method, 'column')) {
                $column = $this->$method();
                if (!$column) continue;
                //默认宽度
                $column = $this->fillColumnOptions($column);
                //排序序号
                if (empty($column['order'])) {
                    $column['order'] = $index;
                }
                //显示属性
                if (empty($column['visible'])) {
                    $column['visible'] = true;
                }
                $index++;
                $result[] = $column;
            }
        }
        //根据order排序
        usort($result, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        $this->tableColumns = $result;
    }

    /**
     * DataManage组件选项
     * @return array
     */
    protected function manageOptions(): array
    {
        return [];
    }

    /**
     * 操作列配置
     * @return array
     */
    public function operation(): array
    {
        return [
            'width'   => 140,
            'show'    => true,
            'fixed'   => 'right',
            'label'   => '操作',
            'mode'    => 'icon',
            'buttons' => ['edit', 'delete']
        ];
    }

    /**
     * 获取表格配置
     * @return array
     */
    public function getTableConfig(): array
    {
        //初始化表格列
        if (!$this->tableColumns) {
            $this->getTableColumns();
        }

        return [
            "manageOptions" => (object)$this->manageOptions(),

            "serverBaseUrl" => $this->serverBaseUrl,
            "serverActions" => $this->serverActions,

            "tabs"      => $this->tabs,
            "tabProp"   => $this->tabProp,
            "activeTab" => $this->activeTab,

            "toolbarButtons" => $this->toolbarButtons,

            "tableSelection"  => $this->tableSelection,
            "tableColumns"    => $this->tableColumns,
            "tableOperations" => $this->operation(),
            "useTreeTable"    => $this->useTreeTable,

            "showSearch"  => $this->showSearch,
            "searchItems" => $this->searchItems,
            "formItems"   => $this->formItems,

            "pk"             => $this->pk,
            "sortField"      => $this->sortField,
            "sort"           => $this->sort,
            "showPagination" => $this->showPagination,
            "pageSizes"      => $this->pageSizes,
            "pageSize"       => $this->pageSize,
        ];
    }

    /**
     * 填充合并表格列配置
     * @param $column
     * @return array
     */
    protected function fillColumnOptions($column): array
    {
        $options = [];
        foreach (['prop', 'type'] as $key) {
            if (isset($column[$key])) {
                //默认选项
                $key = "{$key}.{$column[$key]}";
                if (isset($this->defaultOptions[$key])) {
                    $options = $this->defaultOptions[$key];
                }
            }
        }
        if (!$options) {
            $options = $this->defaultOptions['normal'];
        }
        //合并
        $result = array_merge($options, $column);

        //检测表单信息
        foreach (['form', 'search'] as $key) {
            if (!isset($result[$key]) || !$result[$key]) {
                continue;
            }
            if ($result[$key] === true) {
                $result[$key] = [];
            }
            //默认表单项目类型
            if (empty($result[$key]['type'])) {
                $result[$key]['type'] = $result['type'] ?? 'input';
            }
        }

        return $result;
    }

}