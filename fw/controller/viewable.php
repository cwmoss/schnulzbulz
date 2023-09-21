<?php

namespace xorc\controller;

use Exception;
use xorc\util_http;
use xorc\util;
use xorc\util_names;
use xorc\util_html;
use xorc\view;

/*
    your controller MUST have a router object in $router
*/

trait viewable {

    function make_view($name, array $data = []) {
        return view::new_from_controller($this, $name)->data($data)->set_layout(static::LAYOUT ?? '');
    }
}
