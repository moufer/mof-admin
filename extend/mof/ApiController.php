<?php

namespace mof;

use mof\exception\ApiExceptionHandler;
use think\App;

class ApiController extends BaseController
{
    public function __construct(App $app)
    {
        $app->bind('think\exception\Handle', ApiExceptionHandler::class);
        parent::__construct($app);
    }
}