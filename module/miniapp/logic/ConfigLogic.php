<?php

namespace module\miniapp\logic;

use app\model\Config;
use module\miniapp\model\MiniApp;
use mof\annotation\Inject;
use mof\Logic;

class ConfigLogic extends Logic
{
    #[Inject(Config::class)]
    protected $model;

    #[Inject]
    protected MiniApp $miniapp;

    /**
     * 获取小程序端需要的参数
     * @return array
     */
    public function getMiniappConfig(): array
    {
        $rows = $this->model->where([
            'module'      => $this->miniapp->module,
            'extend_type' => 'miniapp',
            'extend_id'   => $this->miniapp->id
        ])->order('name')->select();

        $result = [];
        $rows->each(function ($row) use (&$result) {
            !empty($row['extra']['miniapp']) && $result[$row['name']] = $row['value'];
        });

        return $this->transformArrayStructure($result);
    }

    /**
     * 格式化配置
     * @param $array
     * @return array
     */
    protected function transformArrayStructure($array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $keys = explode(".", $key);
            $current = &$result;

            foreach ($keys as $index => $keyPart) {
                if (!isset($current[$keyPart])) {
                    $current[$keyPart] = [];
                }

                if ($index === count($keys) - 1) {
                    $current[$keyPart] = $value;
                }

                $current = &$current[$keyPart];
            }
        }

        return $result;
    }
}