<?php

namespace app\controller;

use app\model\app;
use app\model\post;
use app\model\comment;

use Laminas\Diactoros\Response\JsonResponse;

// a json controller

class api {

    function __construct() {
    }

    function hello() {
        return new JsonResponse(['ok' => true, 'res' => 'hello, i am the disco api.']);
    }

    function comments($psr7) {
        $request = json_decode((string) $psr7->getBody(), true);
        $src = $request['url'];
        $app = app::fetch_by_url($src);
        if (!$app) {
            $data = ['total' => 0, 'comments' => []];
        } else {
            $post = post::fetch_by_url($app, $src);
            if (!$post) {
                $data = ['total' => 0, 'comments' => []];
            } else {
                $comments = comment::find_initial($post);
                $data = ['total' => $post->comments_count, 'comments' => $comments];
            }
        }
        return new JsonResponse($data);
    }

    function reply($psr7) {
        $request = json_decode((string) $psr7->getBody(), true);
        $src = $request['url'];
        $app = app::fetch_by_url($src);
        $post = post::fetch_by_url($app, $src);
        $comment = comment::create_reply($post, $request['parent_id'], $request['content']);
        // $data = ['neue antwort', 'hehe'];
        $comments = comment::find_initial($post);
        $data = ['total' => $post->comments_count, 'new_comment' => $comment, 'comments' => $comments];
        return new JsonResponse($data);
    }

    function new_comment($psr7) {
        $request = json_decode((string) $psr7->getBody(), true);
        $src = $request['url'];
        $app = app::fetch_by_url($src);
        if (!$app) {
            dbg("++ no app found");
            $app = app::new_from_url($src, (string) ($request['apptitle'] ?? ""));
            $post = post::new_from_url($app, $src, (string) $request['title'] ?? "");
        } else {
            $post = post::fetch_by_url($app, $src);
            if (!$post) $post = post::new_from_url($app, $src, (string) $request['title'] ?? "");
        }
        $comment = comment::create_for_post($post, $request['content']);
        $post->refresh();
        $comments = comment::find_for_new_comment($post);
        $data = ['total' => $post->comments_count, 'comments' => $comments];
        return new JsonResponse($data);
    }
}
