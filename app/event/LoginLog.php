<?php

namespace app\event;

use think\facade\Db;

/**
 * 记录用户登录记录
 */
class LoginLog
{
    public function handle($params): void
    {
        // 事件监听处理
        $data = [
            'username'   => $params['username'],
            'status'     => $params['status'],
            'ip'         => app('request')->ip(),
            'browser'    => $this->getBrowser(),
            'os'         => $this->getOs(),
            'user_agent' => app('request')->header('user-agent'),
            'login_at'   => date('Y-m-d H:i:s', request()->time())
        ];
        Db::name('admin_login_log')->insert($data);
    }

    /**
     * 获取游客的浏览器信息
     */
    public function getBrowser(): string
    {
        $userAgent = app('request')->header('user-agent');
        $browser = 'unknown';
        $browserVer = '';
        if (preg_match('/MSIE\s([^\s|;]+)/i', $userAgent, $regs)) {
            $browser = 'IE';
            $browserVer = $regs[1];
        } elseif (preg_match('/FireFox\/([^\s]+)/i', $userAgent, $regs)) {
            $browser = 'FireFox';
            $browserVer = $regs[1];
        } elseif (preg_match('/Maxthon([\d]*)\/([^\s]+)/i', $userAgent, $regs)) {
            $browser = 'Maxthon';
            $browserVer = $regs[2];
        } elseif (preg_match('#SE 2([a-zA-Z0-9.]+)#i', $userAgent, $regs)) {
            $browser = 'Sogou';
            $browserVer = $regs[1];
        } elseif (preg_match('/Chrome\/([^\s]+)/i', $userAgent, $regs)) {
            $browser = 'Chrome';
            $browserVer = $regs[1];
        } elseif (preg_match('/Edge([\d]*)\/([^\s]+)/i', $userAgent, $regs)) {
            $browser = 'Edge';
            $browserVer = $regs[2];
        } elseif (preg_match('/UC/i', $userAgent)) {
            $browser = 'UC';
        } elseif (preg_match('/OPR\/([^\s]+)/i', $userAgent, $regs)) {
            $browser = 'Opera';
            $browserVer = $regs[1];
        } elseif (preg_match('/micromessenger/i', $userAgent, $regs)) {
            $browser = 'Wechat';
        } elseif (preg_match('/alipay/i', $userAgent, $regs)) {
            $browser = 'Wechat';
            $browserVer = $regs[1];
        } elseif (preg_match('/baidu/i', $userAgent, $regs)) {
            $browser = 'Baidu';
            $browserVer = $regs[1];
        } elseif (preg_match('/rv:([\d.]+)\) like Gecko/i', $userAgent, $regs)) {
            $browser = 'IE';
            $browserVer = $regs[1];
        }

        return $browser . '(' . $browserVer . ')';
    }

    public function getOs(): string
    {
        $agent = app('request')->header('user-agent');
        if (false !== stripos($agent, 'win')) {
            $versions = [
                '5.1'  => 'XP',
                '5.2'  => 'XP',
                '6.0'  => 'Vista',
                '6.1'  => '7',
                '6.2'  => '8',
                '6.3'  => '8.1',
                '10.0' => '10',
                '10.1' => '11'
            ];
            foreach ($versions as $version) {
                if (preg_match('/nt ' . $version . '/i', $agent)) {
                    return 'Windows ' . $version;
                }
            }
            return 'Windows';
        } else if (false !== stripos($agent, 'linux')) {
            return 'Linux';
        } else if (false !== stripos($agent, 'mac')) {
            return 'macOs';
        } else if (false !== stripos($agent, 'ubuntu')) {
            return 'Ubuntu';
        } else if (false !== stripos($agent, 'android')) {
            return 'Android';
        } else if (false !== stripos($agent, 'iphone')) {
            return 'iPhone';
        } else if (false !== stripos($agent, 'ipad')) {
            return 'iPad';
        } else if (false !== stripos($agent, 'unix')) {
            return 'Unix';
        } else if (false !== stripos($agent, 'bsd')) {
            return 'BSD';
        } else if (false !== stripos($agent, 'symbian')) {
            return 'Symbian';
        } else if (false !== stripos($agent, 'blackberry')) {
            return 'BlackBerry';
        } else if (false !== stripos($agent, 'macintosh')) {
            return 'Macintosh';
        }
        return 'unknown';
    }
}