<?php

namespace module\miniapp\enumeration;

enum WechatMiniappApiEnum: string
{
    case getPhoneNumber = 'wxa/business/getuserphonenumber';
    case getDailyVisitTrend = 'datacube/getweanalysisappiddailyvisittrend';
    case getDailySummary = 'getweanalysisappiddailysummarytrend';
}
