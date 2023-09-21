<?php

namespace app\model;

use xorc\ar\base;

class app extends base {

    static function new_from_url($url, $title) {
        $prefix = parse_url($url) + ['port' => ''];
        $app_prefix = $prefix['host'] . ($prefix['port'] ? ':' . $prefix['port'] : '');
        $app = static::new(['prefix' => $app_prefix, 'title' => $title]);
        $app->save();
        return $app;
    }

    static function fetch_by_url($url) {
        $prefix = parse_url($url) + ['port' => ''];
        $app_prefix = $prefix['host'] . ($prefix['port'] ? ':' . $prefix['port'] : '');
        $res = static::find_first(['prefix' => $app_prefix]);
        dbg("+ app fetch by url", $res);
        return $res;
    }

    static function define_schema() {
        return ['table' => 'apps'];
    }
}
