<?php

namespace module\miniapp\controller;

use module\miniapp\library\MiniappController;
use module\miniapp\logic\StatisticsLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

/**
 * 小程序统计
 */
class Statistics extends MiniappController
{
    #[Inject]
    protected StatisticsLogic $logic;

    protected function formValidate(): void
    {
        $this->formValidate = [
            'rule' => [
                'begin_date|开始日期' => 'dateFormat:Y-m-d|date',
                'end_date|结束日期'   => 'dateFormat:Y-m-d|date|before:' . date('Y-m-d', time() - 86400),
            ]
        ];
        parent::formValidate();
    }

    /**
     * 小程序趋势统计
     * @return Json
     */
    public function index(): Json
    {
        $range = $this->form->get();
        return ApiResponse::success(
            array_values($this->logic->stat($range['begin_date'] ?? '', $range['end_date'] ?? ''))
        );
    }

}