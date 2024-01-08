<?php
namespace app\library;

// 应用请求对象类
class Request extends \think\Request
{
    public function withPut(array $put): static
    {
        $this->put = $put;
        return $this;
    }
}
