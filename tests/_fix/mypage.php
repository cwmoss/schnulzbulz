<?php

namespace myapp\controller;

use xorc\controller\routable;
use xorc\router;

class mypage {
    use routable;

    public function __construct(public router $router) {
    }

    public function hello() {
    }
}

class another_page extends mypage {
}
