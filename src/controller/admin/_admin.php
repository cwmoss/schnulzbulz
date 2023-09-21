<?php

namespace app\controller\admin;

use xorc\router;
use xorc\controller\routable;
use xorc\controller\viewable;

class _admin {
    use routable;
    use viewable;

    const LAYOUT = 'admin';

    function __construct(public router $router) {
    }
}
