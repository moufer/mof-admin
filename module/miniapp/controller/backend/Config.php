<?php

namespace module\miniapp\controller\backend;

use module\miniapp\library\MiniappController;
use module\miniapp\model\MiniApp;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

class Config extends MiniappController
{
    use \app\concern\Config;

    #[Inject]
    protected MiniApp $miniapp;

    public function options(string $module): Json
    {
        if (!$class = \mof\Module::loadConfig($module)) {
            return ApiResponse::error('配置选项不存在');
        }

        $values = $this->values($module, [
            'extend_type' => 'miniapp',
            'extend_id'   => $this->miniapp->id
        ]);
        $form = $class->build($values);
        return ApiResponse::success($form);
    }

    protected function extendInfo(): array
    {
        return [
            'extend_type' => 'miniapp',
            'extend_id'   => $this->miniapp->id
        ];
    }
}