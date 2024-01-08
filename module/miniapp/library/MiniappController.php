<?php

namespace module\miniapp\library;

use app\library\AdminController;
use module\miniapp\model\MiniApp;

class MiniappController extends AdminController
{
    /** @var MiniApp 小程序表模型 */
    protected MiniApp $miniapp;

    protected function initialize(): void
    {
        parent::initialize();
        $this->miniapp = $this->request->miniapp;
    }
}