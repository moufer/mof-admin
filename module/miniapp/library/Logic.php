<?php

namespace module\miniapp\library;

use module\miniapp\model\MiniApp;
use mof\annotation\Inject;

class Logic extends \mof\Logic
{
    /**
     * @var MiniApp 操作的小程序平台模型，默认为URL上指定的小程序平台
     */
    #[Inject]
    protected MiniApp $miniapp;

    /**
     * 设置操作的小程序平台模型
     * @param MiniApp $miniapp
     * @return $this
     */
    public function withMiniapp(MiniApp $miniapp): static
    {
        $this->miniapp = $miniapp;
        return $this;
    }
}