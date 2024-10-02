<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/24 21:37
 */

namespace app\event;

use think\facade\Cache;

class SetSessionId
{

    public function handle(): void
    {
        $xSessionId = app('request')->header('X-Session-Id', '');
        if ($xSessionId) {
            $cacheKey = "xSession:{$xSessionId}";
            if ($data = Cache::get($cacheKey)) {
                if ($data['ip'] !== app('request')->ip()) {
                    $xSessionId = null;
                }
            } else {
                $xSessionId = null;
            }
        }
        if (!$xSessionId) {
            $xSessionId = md5(uniqid());
            $cacheKey = "xSession:{$xSessionId}";
            $this->createSession($cacheKey);
        }
        app('request')->xSessionId = $xSessionId;
    }

    protected function createSession($cacheKey): void
    {
        $ip = app('request')->ip();
        $time = app('request')->server('REQUEST_TIME', time());
        $value = ['ip' => $ip, 'time' => $time];
        Cache::set($cacheKey, $value, 1800);
    }

}