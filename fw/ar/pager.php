<?php

namespace xorc\ar;

class pager {

    public bool $more = false;
    public bool $less = false;
    public int $next = 0;
    public int $prev = 0;
    public int $totalpages = 0;

    public function __construct(public int $total, public int $limit = 10, public int $currentpage = 1) {
        $this->calculate($limit, $currentpage);
    }

    public function set_total(int $total) {
        $this->total = $total;
        $this->calculate($this->limit, $this->currentpage);
    }

    public function calculate($limit, $current = 1) {
        $max = ceil($this->total / $limit);
        if (!$max) $max = 1;
        $this->less = $current > 1;
        $this->more = $current < $max;
        $this->prev = $this->less ? $current - 1 : 1;
        $this->next = $this->more ? $current + 1 : $current;
        $this->totalpages = $max;
    }
}
