<?php

namespace mof\utils;

class Arr
{
    /**
     * 递归生成树形结构
     * @param array $data
     * @param int $pid
     * @return array
     */
    public static function tree(array $data, int $pid = 0): array
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item['pid'] == $pid) {
                $item['children'] = self::tree($data, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 生成树形结构
     * @param $menuItems array 菜单列表
     * @param $parentId int 父级ID
     * @return array 菜单树
     */
    public static function generateMenuTree(array $menuItems, int $parentId = 0): array
    {
        $menuTree = array();

        foreach ($menuItems as $menu) {
            if ($menu['pid'] == $parentId) {
                $menuItem = $menu;
                $menu['children'] = [];

                $children = self::generateMenuTree($menuItems, $menu['id']);
                if (!empty($children)) {
                    $menuItem['children'] = $children;
                }

                $menuTree[] = $menuItem;
            }
        }

        return $menuTree;
    }

    /**
     * 生成级联选择器
     * @param array $data 数据
     * @param int $pid 父级ID
     * @param array $keyAlias
     * @return array
     */
    public static function generateCascader(array $data, int $pid = 0, array $keyAlias = []): array
    {
        $result = [];
        foreach ($data as $item) {
            if ($item['pid'] == $pid) {
                $children = self::generateCascader($data, (int)$item['id'], $keyAlias);
                $result[] = [
                    'value'    => $item[$keyAlias['id'] ?? 'id'],
                    'label'    => $item[$keyAlias['title'] ?? 'title'],
                    'children' => $children,
                ];
            }
        }
        return $result;
    }


}