<?php

namespace mof;

class Request extends \think\Request
{
    public function searcher(): Searcher
    {
        $searcher = new Searcher();
        $searcher->params($this->param('params/a', []));
        $searcher->pageSize($this->get('page_size/d', 10));
        $order = $this->get('order/a', []);
        if (!empty($order['field'])) {
            $searcher->order([$order['field'] => $order['order'] ?? 'asc']);
        }
        return $searcher;
    }

}