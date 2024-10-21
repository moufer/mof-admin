<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/30 20:28
 */

namespace app\logic;

use app\library\Interface\TotalInterface;
use app\model\Module;
use app\model\Total;
use mof\annotation\Inject;
use mof\Logic;
use think\App;

class TotalLogic extends Logic
{
    #[Inject(Total::class)]
    protected $model;

    protected array $totalGroup = [];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->totalGroup = $this->getTotalGroup();
    }

    /**
     * 从数据库获取当日数据
     * @return array
     */
    public function getTotalData(): array
    {
        $rows = $this->model->where('day', date('Y-m-d'))->column('*');
        $todayData = [];
        foreach ($rows as $row) {
            $todayData[$row['module'] . '.' . $row['name']] = $row['count'];
        }

        $result = [];
        foreach ($this->totalGroup as $name => $title) {
            list($module, $key) = explode('.', $name);
            $totalClass = $this->markTotalClass($module);
            $result[$name] = [
                'title' => $title,
                'today' => $todayData[$name] ?? 0,
                'total' => $totalClass->$key()
            ];
        }
        return $result;
    }

    public function getTrendData(): array
    {
        //获取最近15天的数据
        $rows = $this->model->whereTime('day', '>=', date('Y-m-d', strtotime('-14 day')))
            ->order('day asc')
            ->column('*');

        $data = [];
        foreach ($rows as $row) {
            $data["{$row['module']}.{$row['name']}"][] = [
                'day'   => $row['day'],
                'count' => $row['count']
            ];
        }

        $charts = [];
        $totalFiles = $this->loadTotalFiles();
        foreach ($totalFiles as $module => $className) {
            $totalClass = $this->markTotalClass($module);
            //获取趋势信息
            $trends = $totalClass->trends();
            $chart = [
                'tooltip' => ['trigger' => 'axis'],
                'legend'  => ['icon' => 'circle'],
                'xAxis'   => [
                    'type'     => 'category',
                    'axisTick' => [
                        'alignWithLabel' => true
                    ],
                    'data'     => array_map(fn($i) => date('Y-m-d', strtotime((-14 + $i) . " day")), range(0, 14))
                ],
                'yAxis'   => ['type' => 'value'],
            ];
            foreach ($trends as $trade) {
                $chart['extra'] = ['title' => $trade['title']];
                $chart['series'] = array_map(function ($name) use ($module, $data, &$result) {
                    $key = "$module.$name";
                    return [
                        'name'   => $this->totalGroup[$key],
                        'type'   => 'line',
                        'data'   => isset($data[$key]) ? $this->formatItemTrades($data[$key]) : [],
                        'smooth' => true,
                    ];
                }, $trade['names']);
            }
            $charts[] = $chart;
        }
        return $charts;
    }

    /**
     * 对找到的Total.php文件，进行解析
     * 通过反射获取类中的public方法，方法名作为键，并获取这个方法的 #[Description] 注解，获取注解的第一个参数，作为值，返回数组
     */
    public function getTotalGroup(): array
    {
        $totalInfo = [];
        $totalFiles = $this->loadTotalFiles();
        foreach ($totalFiles as $module => $className) {
            $instance = $this->markTotalClass($module);
            foreach ($instance->totals() as $key => $title) {
                $totalInfo["{$module}.{$key}"] = $title;
            }
        }

        return $totalInfo;
    }

    /**
     * 遍历模块，找模块目录下\front\Total.php文件
     */
    public function loadTotalFiles(): array
    {
        global $files;

        if ($files) return $files;

        $files = [];
        $modules = Module::enabledModules();
        foreach ($modules as $module) {
            $name = $module['name'];
            $file = \mof\Module::path($name) . 'front' . DIRECTORY_SEPARATOR . 'Total.php';
            if (is_file($file)) {
                $className = "\\module\\{$name}\\front\\Total";
                if (class_exists($className)) {
                    $files[$name] = $className;
                }
            }
        }
        return $files;
    }

    /**
     * 获取模块统计类实例
     * @param string $module
     * @return mixed
     */
    protected function markTotalClass(string $module): TotalInterface
    {
        global $instances;
        if (empty($instances[$module])) {
            $totalClass = "\\module\\{$module}\\front\\Total";
            $instances[$module] = new $totalClass();
        }
        return $instances[$module];
    }

    protected function formatItemTrades(?array $data): array
    {
        $result = [];
        $data = $data ? array_column($data, 'count', 'day') : [];
        //近15天日历，并填充
        for ($i = 14; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i day"));
            $result[] = $data[$day] ?? 0;
        }

        return $result;
    }
}