<?php

namespace app\model;

use xorc\ar\base;

class comment extends base {

    static function find_initial(post $post) {
        $q = self::new_query_builder()->where('post_id', $post->id)->order('created_at DESC')->limit(20);
        $comments = iterator_to_array(self::find_all($q));
        return $comments;
    }

    static function find_for_new_comment($post) {
        $q = self::new_query_builder()->where('post_id', $post->id)->order('created_at DESC')->limit(20);
        $comments = iterator_to_array(self::find_all($q));
        return $comments;
    }
    static function create_reply(post $post, $parent_id, $text) {
        $c = self::new(['post_id' => $post->id, 'parent_id' => $parent_id, 'content' => $text, 'user_name' => '']);
        $c->save();
        return $c;
    }
    static function create_for_post(post $post, $text) {
        $c = new self;
        $c->set(['post_id' => $post->id, 'content' => $text, 'user_name' => '']);
        $c->save();
        return $c;
    }

    static function define_schema() {
        return ['table' => 'comments'];
    }
}
