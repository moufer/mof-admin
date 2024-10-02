<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/23 16:23
 */

namespace app\model;

use mof\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class Captcha extends Model
{
    protected $name = 'system_captcha';

    protected $updateTime = false;

    /**
     * 获取验证码
     * @param string $accountType 账号类型 email,mobile
     * @param string $account 账号
     * @param string $event 事件
     * @param string $code 验证码
     * @param int $expire 有效期(秒)
     * @return Captcha|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getValidCode(string $accountType, string $account, string $event, string $code, int $expire): ?static
    {
        return $this->where('account_type', $accountType)
            ->where('account', $account)
            ->where('event', $event)
            ->where('code', $code)
            ->whereTime('create_at', '>=', time() - $expire)
            ->find();
    }

    /**
     * 获取最新的验证码
     * @param string $accountType
     * @param string $account
     * @param string $event
     * @param int $expire
     * @return $this|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getCodeByEvent(string $accountType, string $account, string $event, int $expire): ?static
    {
        return $this->where('account_type', $accountType)
            ->where('account', $account)
            ->where('event', $event)
            ->whereTime('create_at', '>=', time() - $expire)
            ->order('id', 'desc')
            ->find();
    }

    /**
     * 获取最近发送的验证码
     * @param string $accountType
     * @param string $account
     * @param string $event 事件
     * @param int $expire 有效期(秒)
     * @return Captcha|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRecentCode(string $accountType, string $account, string $event, int $expire): ?static
    {
        return $this->where('account_type', $accountType)
            ->where('account', $account)
            ->where('event', $event)
            ->whereTime('create_at', '>=', time() - $expire)
            ->find();
    }

    /**
     * 删除验证码
     * @param string $accountType
     * @param string $account
     * @param string $event
     * @return bool
     */
    public function deleteCode(string $accountType, string $account, string $event): bool
    {
        return $this->where('account_type', $accountType)
            ->where('account', $account)
            ->where('event', $event)
            ->delete();
    }

}