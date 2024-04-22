<?php

namespace module\miniapp\logic;

use EasyWeChat\Kernel\Exceptions\BadResponseException;
use module\miniapp\enumeration\WechatMiniappApiEnum;
use module\miniapp\model\MiniApp;
use module\miniapp\model\Statistics;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\Logic;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;


class StatisticsLogic extends Logic
{
    #[Inject]
    protected MiniApp $miniapp;

    /**
     * 小程序访问统计数据
     * @param string $beginData Y-m-d
     * @param string $endData Y-m-d
     * @return array 格式：[ 'Y-m-d' => [ '...'] ]
     */
    public function stat(string $beginData, string $endData): array
    {
        $yesterdayStamp = strtotime('yesterday midnight');
        $beginStamp = $beginData ? strtotime($beginData) : $yesterdayStamp;
        $endStamp = $beginData ? strtotime($endData) : $yesterdayStamp;

        if ($endStamp < $beginStamp) {
            throw new LogicException('结束日期不能早于起始日期');
        } else if ($endStamp - $beginStamp > 2592000) {
            throw new LogicException('查询范围不能超过30天');
        }

        $beginDate = date('Ymd', $beginStamp);
        $endDate = date('Ymd', $endStamp);

        $rows = Statistics::where('ma_id', $this->miniapp->id)
            ->whereTime('def_date', 'between', [$beginDate, $endDate])
            ->order('def_date', 'desc')
            ->column('*', 'def_date');

        $result = [];
        $columns = [
            'session_cnt', 'visit_pv', 'visit_uv', 'visit_uv_new', 'stay_time_uv',
            'stay_time_session', 'visit_depth'
        ];

        $date = $beginDate;
        //遍历的日期，获取对应日期的数据
        while ($date <= $endDate) {
            $key = date('Y-m-d', strtotime($date));
            if (isset($rows[$key])) {
                $result[$key] = $rows[$key];
            } else {
                try {
                    //如果本地数据不存在，则从小程序平台获取对应日期的数据
                    $result[$key] = $this->getTrends($date);
                    $result[$key]['def_date'] = $key;
                    //写入 statistics 表
                    $data = array_reduce($columns,
                        fn($carry, $item) => $carry + [$item => $result[$key][$item]], []);
                    $data['ma_id'] = $this->miniapp->id;
                    $data['def_date'] = $key;
                    //保存到数据库
                    Statistics::create($data);
                } catch (\RuntimeException $e) {
                    if ($e->getCode() !== 61503) {
                        throw new LogicException($e->getMessage() . "({$e->getCode()})");
                    } else {
                        $result[$key] = array_fill_keys($columns, 0);
                        $result[$key]['def_date'] = $key;
                    }
                }
            }
            $date = date('Ymd', strtotime($date . ' +1 day'));
        }
        return $result;
    }

    /**
     * 从小程序接口获取指定日期的统计趋势
     * @param string $day 指定日期 格式：Ymd
     * @return array
     */
    protected function getTrends(string $day): array
    {
        try {
            $client = $this->miniapp->sdk->getClient();
            $data = $client->postJson(WechatMiniappApiEnum::getDailyVisitTrend->value, [
                'begin_date' => $day,
                'end_date'   => $day,
            ])->toArray();

            if ($data['errcode'] ?? 0) {
                throw new \RuntimeException($data['errmsg'], $data['errcode'] ?? 1);
            }
        } catch (ExceptionInterface|BadResponseException $e) {
            throw new LogicException($e->getMessage());
        }

        return $data['list'][0];
    }
}