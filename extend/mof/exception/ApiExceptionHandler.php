<?php

namespace mof\exception;

use mof\ApiResponse;
use think\App;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class ApiExceptionHandler extends Handle
{
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        AuthTokenException::class,
        LogicException::class
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->isJson = true;
    }

    public function render(\think\Request $request, Throwable $e): Response
    {
        if ($e instanceof ValidateException || $e instanceof DataNotFoundException) {
            return ApiResponse::fail($e->getMessage(), $e->getCode() ?: 1);
        } else if ($e instanceof LogicException || $e instanceof NoPermissionException) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 2);
        } else if ($e instanceof AuthTokenException) {
            return ApiResponse::needLogin($e->getMessage());
        } else {
            return parent::render($request, $e);
        }
    }
}