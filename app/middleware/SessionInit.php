<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/25 10:53
 */

namespace app\middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;
use think\Session;

class SessionInit
{
    public function __construct(protected App $app, protected Session $session)
    {
    }

    /**
     * Session初始化
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Session初始化
        $sessionId = $request->header('X-Session-Id', '');
        if (!$sessionId || strlen($sessionId) != 32 || !preg_match('/^[a-z0-9]{32}$/i', $sessionId)) {
            $sessionId = md5(uniqid());
        }

        $this->session->setId($sessionId);
        $this->session->init();

        $request->withSession($this->session);

        /** @var Response $response */
        $response = $next($request);
        $response->setSession($this->session);

        $header = $response->getHeader();
        $header['X-Session-Id'] = $sessionId;

        return $response->header($header);
    }

    public function end(Response $response): void
    {
        $this->session->save();
    }

//    protected function createSessionId($cacheKey): void
//    {
//        $ip = app('request')->ip();
//        $time = app('request')->server('REQUEST_TIME', time());
//        $value = ['ip' => $ip, 'time' => $time];
//        Cache::set($cacheKey, $value, 1800);
//    }
}