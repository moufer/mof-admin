<?php

namespace mof\front;

class Tree
{
    protected array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 获取树形结构的数据
     * @param $pid int 父级id
     * @param $labelAlias string 标签别名
     * @return array
     */
    public function getData(int $pid = 0, string $labelAlias = 'label'): array
    {
        $tree = [];
        $fields = ['id', $labelAlias, 'children'];
        foreach ($this->data as $item) {
            if ($item['pid'] == $pid) {
                $item['children'] = $this->getData($item['id'], $labelAlias);
                //从$item里只获取id,label, children
                $item = array_intersect_key($item, array_flip($fields));
                if ('label' !== $labelAlias) {
                    $item['label'] = $item[$labelAlias];
                    unset($item[$labelAlias]);
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 获取树形结构的数据
     * @param $pid int 父级id
     * @return array
     */
    public function getTableTree(int $pid = 0): array
    {
        $tree = [];
        foreach ($this->data as $item) {
            if ($item['pid'] == $pid) {
                $item['children'] = $this->getData($item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}