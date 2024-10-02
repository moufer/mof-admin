<?php

namespace mof;

class Validate extends \think\Validate
{
    /**
     * 获取已定义的规则
     * @return array
     */
    public function getRuleKeys(): array
    {
        return array_map(function ($key) {
            //去掉key里"|"后面的内容
            return substr($key, 0, strpos($key, '|'));
        }, array_keys($this->rule));
    }


}