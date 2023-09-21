<?php

namespace app\controller\admin;

use app\model\comment;
use xorc\ar\querybuilder;

class user extends _admin {

    public $auto_layout = true;

    public $_post_render = false;

    const LAYOUT = 'users';

    function index() {

        return "index";
    }

    function pages() {
    }
}
