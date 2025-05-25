<?php

namespace mof;

use Exception;
use mof\exception\AuthTokenException;
use mof\exception\LogicException;
use think\db\exception\DataNotFoundException;
use think\exception\ValidateException;
use think\response\Json;

class ApiResponse
{
    /**
     * 操作成功
     */
    public static function success($data = null, $errmsg = 'ok'): Json
    {
        return self::json($data, $errmsg);
    }

    /**
     * 操作失败
     */
    public static function fail($errmsg = 'warn', $errcode = 1): Json
    {
        return self::json(null, $errmsg, $errcode);
    }

    /**
     * 操作错误
     */
    public static function error($errmsg = 'error', $errcode = 2): Json
    {
        return self::json(null, $errmsg, $errcode);
    }

    /**
     * 操作异常
     * @param Exception $e
     * @return Json
     * @throws Exception
     */
    public static function exception(Exception $e): Json
    {
        if ($e instanceof ValidateException || $e instanceof DataNotFoundException) {
            return self::fail($e->getMessage(), $e->getCode() ?: 1);
        } else if ($e instanceof LogicException) {
            return self::error($e->getMessage(), $e->getCode() ?: 2);
        } else if ($e instanceof AuthTokenException) {
            return self::fail($e->getMessage(), 401);
        } else {
            throw $e;
        }
    }

    /**
     * 数据不存在
     * @param string $errmsg
     * @return Json
     */
    public static function dataNotFound(string $errmsg = ''): Json
    {
        return self::json(null, $errmsg ?: '数据不存在', 404)->code(404);
    }

    /**
     * 请求不存在
     * @param string $errmsg
     * @return Json
     */
    public static function pageNotFound(string $errmsg = ''): Json
    {
        return self::json(null, $errmsg ?: '请求不存在', 404)->code(404);
    }

    /**
     * 返回用户未登录提示
     */
    public static function needLogin($errmsg = ''): Json
    {
        return self::json(null, $errmsg ?: '请先登录', 401)->code(401);
    }

    /**
     * 返回权限不足
     * @param string $errmsg
     * @return Json
     */
    public static function noPermission(string $errmsg = ''): Json
    {
        return self::json(null, $errmsg ?: '权限不足', 403)->code(403);
    }

    /**
     * 返回json
     * @param $data
     * @param string $errmsg
     * @param int $errcode
     * @return Json
     */
    public static function json($data = null, string $errmsg = '', int $errcode = 0): Json
    {
        $result = [
            'errcode' => $errcode,
            'errmsg'  => $errmsg,
            'data'    => $data
        ];
        return json($result);
    }

}