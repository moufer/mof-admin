<?php

namespace module\miniapp\controller;

use module\miniapp\enumeration\WechatMiniappApiEnum;
use module\miniapp\library\MiniappController;
use module\miniapp\model\Statistics as StatisticsModel;
use mof\ApiResponse;
use EasyWeChat\Kernel\Exceptions\BadResponseException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 小程序统计
 */
class Statistics extends MiniappController
{
    /**
     * 小程序趋势统计
     * @return Json
     */
    public function index(): Json
    {
        $yesterday = date('Ymd', time() - 86400);
        $beginDate = str_replace('-', '', $this->request->get('begin_date', $yesterday));
        $endDate = str_replace('-', '', $this->request->get('end_date', $yesterday));
        //日期格式是否是Ymd
        if (!preg_match('/^\d{8}$/', $beginDate) || !preg_match('/^\d{8}$/', $endDate)) {
            return ApiResponse::error('日期格式不正确');
        }
        //判断$beginDate的最大值必须是昨天
        if ($beginDate > $yesterday) {
            return ApiResponse::error('起始日期不能大于昨天');
        }
        //判断$endDate必须小于等于$beginDate
        if ($endDate < $beginDate) {
            return ApiResponse::error('结束日期不能小于起始日期');
        }
        //判断范围是否超过了30天，如果超过，抛出异常
        if (abs(strtotime($endDate) - strtotime($beginDate)) > 2592000) {
            return ApiResponse::error('日期范围不能超过30天');
        }
        $rows = StatisticsModel::where('ma_id', $this->miniapp->id)
            ->whereTime('def_date', 'between', [$beginDate, $endDate])
            ->order('def_date', 'desc')
            ->column('*', 'def_date');
        //遍历 $beginDate 到 $endDate 的日期，从 $rows 中获取对应日期的数据，如果数据不存在，则通过getTrends方法获取对应日期的数据
        $result = [];
        $columns = [
            'session_cnt', 'visit_pv', 'visit_uv', 'visit_uv_new', 'stay_time_uv',
            'stay_time_session', 'visit_depth'
        ];
        $date = $beginDate;
        while ($date <= $endDate) {
            //Ymd转Y-m-d
            $key = date('Y-m-d', strtotime($date));
            if (isset($rows[$key])) {
                $result[$key] = $rows[$key];
            } else {
                try {
                    $result[$key] = $this->getTrends($date);
                    $result[$key]['def_date'] = $key;
                    //写入 statistics 表
                    $data = array_reduce($columns,
                        fn($carry, $item) => $carry + [$item => $result[$key][$item]], []);
                    $data['ma_id'] = $this->miniapp->id;
                    $data['def_date'] = $key;
                    StatisticsModel::create($data);
                } catch (\RuntimeException $e) {
                    if ($e->getCode() !== 61503) {
                        return ApiResponse::error($e->getMessage() . "({$e->getCode()})");
                    } else {
                        $result[$key] = array_fill_keys($columns, 0);
                        $result[$key]['def_date'] = $key;
                    }
                }
            }
            $date = date('Ymd', strtotime($date . ' +1 day'));
        }
        return ApiResponse::success(array_values($result));
    }

    /**
     * @param $day
     * @return array
     */
    private function getTrends($day): array
    {
        try {
            $client = $this->miniapp->easyWechatMiniApp->getClient();
            $data = $client->postJson(WechatMiniappApiEnum::getDailyVisitTrend->value, [
                'begin_date' => $day,
                'end_date'   => $day,
            ])->toArray();
            if ($data['errcode'] ?? 0) {
                throw new \RuntimeException($data['errmsg'], $data['errcode'] ?? 1);
            }
        } catch (ExceptionInterface|BadResponseException $e) {
            throw new ValidateException($e->getMessage());
        }

        return $data['list'][0];
    }
}