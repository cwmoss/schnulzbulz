<?php

namespace app\model;

use xorc\ar\base;

class post extends base {

    static function new_from_url(app $app, $url, $title) {
        $url = parse_url($url);
        $post = static::new(['app_id' => $app->id, 'path' => $url['path'], 'title' => $title]);
        $post->save();
        return $post;
    }

    static function fetch_by_url(app $app, $url) {
        $url = parse_url($url);
        return static::find_first(['app_id' => $app->id, 'path' => $url['path']]);
    }

    static function define_schema() {
        return ['table' => 'posts'];
    }
}
