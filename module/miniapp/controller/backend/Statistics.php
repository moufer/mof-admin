<?php

namespace module\miniapp\controller\backend;

use module\miniapp\library\MiniappController;
use module\miniapp\logic\admin\StatisticsLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '访问统计', url: 'miniapp/statistics', actions: 'index', sort: 1, icon: 'Histogram',
    group: 'common', category: 'miniapp'
)]
class Statistics extends MiniappController
{
    #[Inject]
    protected StatisticsLogic $logic;

    /**
     * 小程序趋势统计
     * @return Json
     */
    public function index(): Json
    {
        $range = $this->request->withValidate([
            'begin_date|开始日期' => 'dateFormat:Y-m-d|date',
            'end_date|结束日期'   => 'dateFormat:Y-m-d|date|before:' . date('Y-m-d', time() - 86400),
        ])->get();
        return ApiResponse::success(
            array_values($this->logic->stat($range['begin_date'] ?? '', $range['end_date'] ?? ''))
        );
    }

}