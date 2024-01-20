<?php

namespace mof\concern\model;

trait Form
{
    /**
     * @var array 允许提交的字段
     */
    protected array $formFields = [];

    /**
     * 获取允许提交的字段列表
     * @return array
     */
    public static function getFormFields(): array
    {
        return (new static())->formFields;
    }

}