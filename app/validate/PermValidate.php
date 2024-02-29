<?php

namespace app\validate;

use mof\Validate;

class PermValidate extends Validate
{
    protected $rule = [
        'title'  => 'require',
        'type'   => 'require|checkType',
        'module' => 'checkModule',
        'pid'    => 'requireIf:type,action|checkPid',
        'url'    => 'requireIf:type,menu',
        'perm'   => 'requireIf:type,action',
        'status' => 'require',
    ];

    protected $message = [
        'title.require'  => '名称不能为空',
        'type.require'   => '权限类型不能为空',
        'module.require' => '所属模块不能为空',
        'pid.requireIf'  => '所属上级不能为空',
        'url.requireIf'  => '菜单权限必须填写URL',
        'perm.requireIf' => '行为权限必须填写权限标识',
        'status.require' => '状态不能为空',
    ];

    protected function checkType($value, $rule, $data): bool|string
    {
        if (!in_array($value, ['group', 'menu', 'action'])) {
            return '选择的权限类型无效';
        }
        if ($value === 'menu' && empty($data['url'])) {
            return '菜单类型必须填写URL';
        }
        if ($value === 'action') {
            if (empty($data['perm'])) {
                return '行为类型必须填写权限标识';
            } else if (empty($data['pid'])) {
                return '行为类型必须选择上级';
            }
        }
        return true;
    }

    protected function checkModule($value, $rule, $data): bool|string
    {
        if ($data['type'] !== 'action' && empty($value)) {
            return '所属模块不能为空';
        }
        return true;
    }

    protected function checkPid(array $value, $rule, $data): bool|string
    {
        //$value是数组字段
        $value = (int)array_pop($value);
        if ($value > 0) {
            $parent = \app\model\Perm::find($value);
            if (!$parent) {
                return '上级权限不存在';
            }
            if ($data['type'] === 'action' && $parent['type'] !== 'menu') {
                return '行为类型权限的上级必须为菜单类型权限' . $value . $parent['type'];
            } else if ($data['type'] === 'menu' && $parent['type'] !== 'group') {
                return '菜单类型权限的上级必须为分组类型权限';
            } else if ($data['type'] === 'group' && $parent['type'] !== 'group') {
                return '分组类型权限的上级必须为分组类型权限';
            }
        }
        return true;
    }
}