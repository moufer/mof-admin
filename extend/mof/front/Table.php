<?php

namespace mof\front;

use mof\utils\DictArray;
use ReflectionClass;
use think\helper\Str;

/**
 * 前端数据管理表格配置
 */
abstract class Table
{
    /** @var string 模块名 */
    protected string $module = '';
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
    /** @var array|string 表格选项 */
    protected array|string $tabs      = [];
    protected string       $tabProp   = '';
    protected string       $activeTab = '';

    /** @var array|string[] 操作条按钮 */
    protected array $toolbarButtons = ['add', 'delete', 'status', 'refresh', 'search'];

    /** @var string 主键名称 */
    protected string $pk = 'id';
    /** @var bool 允许多选 */
    protected bool $tableSelection = true;
    /** @var string 是否允许多选的表达式 */
    protected string $tableSelectionExpr = '';
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
        'normal'        => ['width' => '*', 'align' => 'center'],
        'prop.id'       => ['width' => 80, 'align' => 'center'],
        'prop.sort'     => ['width' => 80, 'align' => 'center'],
        'type.datetime' => ['width' => 115, 'align' => 'center'],
        'type.icon'     => ['width' => 80, 'align' => 'center'],
        'type.media'    => ['width' => 120, 'align' => 'center'],
    ];

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

        !$this->table && $this->table = Str::snake(substr(array_pop($class), 0, -5));
        !$this->module && $this->module = 'app' === $class[0] ? 'system' : $class[1];

        //自动替换接口基础地址里的变量
        $this->serverBaseUrl = $this->parseServerBaseUrl();
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
                if (!$column) {
                    continue;
                }
                //如果没有定义prop属性，从方法名获取
                if (empty($column['prop'])) {
                    $propName = substr($method, 6); // 去掉'column'前缀
                    $column['prop'] = Str::snake($propName); // 驼峰转下划线
                }
                //填充默认选项
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
     * 自定义按钮组格式：['name'=>'按钮名','type'=>'按钮类型','icon'=>'mode=icon时填写','command'='按钮点击后出发点的命令']
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

        //tabs是枚举类时，转换为数组
        if ($this->tabs && is_string($this->tabs) && class_exists($this->tabs)) {
            //检测$this->tabs是不是枚举类
            $ref = new ReflectionClass($this->tabs);
            if ($ref->isEnum()) {
                $this->tabs = call_user_func([$this->tabs, 'toDict'])->toElementData()->toTabs('label', 'value');
                if (!$this->activeTab) {
                    $this->activeTab = $this->tabs[0]['name'];
                }
            }
        }

        return [
            "manageOptions" => (object)$this->manageOptions(),

            "serverBaseUrl" => $this->serverBaseUrl,
            "serverActions" => $this->serverActions,

            "tabs"      => $this->tabs,
            "tabProp"   => $this->tabProp,
            "activeTab" => $this->activeTab,

            "toolbarButtons" => $this->toolbarButtons,

            "tableSelection"     => $this->tableSelection,
            "tableSelectionExpr" => $this->tableSelectionExpr,

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
        //检测枚举选项
        if (isset($column['type']) && in_array($column['type'], ['select', 'radio']) && $column['options'] instanceof DictArray) {
            $column['options'] = $column['options']->toElementData()->toSelectOptions();
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

    /**
     * 解析serverBaseUrl
     * @return string
     */
    protected function parseServerBaseUrl(): string
    {
        $mapping = [
            'module' => $this->module,
            'table'  => $this->table,
        ];

        //替换接口基础地址里的{module}和{table}
        $url = str_replace(['{module}', '{table}'], $mapping, $this->serverBaseUrl);
        //查找{xxx}变量
        $matches = [];
        preg_match_all('/\{(.*?)}/', $url, $matches);
        //替换变量
        foreach ($matches[1] as $var) {
            if (!isset($mapping[$var])) {
                //从request参数里获取值
                if (app()->request->has($var)) {
                    $mapping[$var] = app()->request->get($var);
                } else {
                    continue;
                }
            }
            $url = str_replace("{{$var}}", $mapping[$var], $url);
        }
        return $url;
    }
}
