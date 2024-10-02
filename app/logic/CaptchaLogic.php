<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/23 16:44
 */

namespace app\logic;

use app\model\Captcha;
use Closure;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\Logic;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class CaptchaLogic extends Logic
{
    /** @var Captcha */
    #[Inject(Captcha::class)]
    protected $model;

    /** @var int 有效期 */
    protected int $expire = 300; // 10分钟

    /**
     * 生成验证码
     * @param string $accountType 账号类型 email,mobile
     * @param string $account 账号
     * @param string $event 事件
     * @param Closure $callback 回调函数
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function createCaptcha(string $accountType, string $account, string $event, Closure $callback): bool
    {
        // 检查是否在1分钟内已经发送过
        if ($this->model->getRecentCode($accountType, $account, $event, 60)) {
            throw new LogicException('请勿重复发送验证码，请稍后再试');
        }

        $this->model->startTrans();
        try {
            //删除之前的验证码
            $this->model->deleteCode($accountType, $account, $event);

            //新建保存验证码
            $model = $this->save([
                'account_type' => $accountType,
                'account'      => $account,
                'event'        => $event,
                'code'         => mt_rand(1000, 9999),
                'ip'           => request()->ip(),
            ]);

            //业务回调（短信，邮件等）
            $callback([
                'code' => $model->code,
                'time' => round($this->expire / 60)
            ]);

            $this->model->commit();
        } catch (\Exception $e) {
            $this->model->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 验证验证码
     * @param string $accountType 账号类型 email,mobile
     * @param string $account 账号
     * @param string $event 事件
     * @param string $code 验证码
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function verifyCaptcha(string $accountType, string $account, string $event, string $code): bool
    {
        $try = 3; // 最多尝试3次
        $captcha = $this->model->getCodeByEvent($accountType, $account, $event, $this->expire);
        if ($captcha) {
            if ($captcha->code === $code) {
                $this->model->deleteCode($accountType, $account, $event);
                return true;
            }
            //限制次数，避免暴力破解
            if ($captcha->times >= $try - 1) {
                $this->model->deleteCode($accountType, $account, $event);
                throw new LogicException('验证码错误次数过多，请重新发送');
            } else {
                $captcha->save(['times' => $captcha->times + 1]);
                return false;
            }
        }
        return false;
    }
}