<?php

namespace app\controller;

use app\library\Controller;
use think\response\Jsonp;

class Ueditor extends Controller
{
    public function index($action): Jsonp
    {
        return match ($action) {
            'config' => $this->config(),
            default => jsonp(['error' => 'action not found']),
        };
    }

    protected function config(): Jsonp
    {
        return jsonp([]);
    }

}