<?php

namespace app\controller\admin;

use app\model\comment;
use xorc\ar\querybuilder;

class dashboard extends _admin {

    public $auto_layout = true;
    public $_post_render = false;

    function index($id = 1) {
        $page = $id;
        $query = querybuilder::new()->limit(5, $page);
        dbg("find-all");
        // $comments = iterator_to_array(comment::find_all($query));
        $comments = comment::find_all($query);
        dbg("count-all");
        $pager = comment::get_pager($query);
        return $this->make_view("index", ['comments' => $comments, 'pager' => $pager]);
    }

    function pages() {
    }
}
