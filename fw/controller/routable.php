<?php

namespace xorc\controller;

use Exception;
use xorc\util_http;
use xorc\util;
use xorc\util_names;
use xorc\util_html;

/*
    your controller MUST have a router object in $router
*/

trait routable {

    function link($to, $text, $parms = []) {
        // dd("link!", $to, $text, $parms);

        return sprintf(
            '<a href="%s">%s</a>',
            util_html::escape_attribute($this->url($to, $parms)),
            $text
        );
    }

    function url($to = "", $parms = []) {
        if (is_array($to)) {
            [$to, $parms] = $to + ["", []];
        }

        // TODO: route to self? not supported anymore y/n? find a way somehow?
        if (!$to) {
            throw new \Exception('selfurl is not supported');
        } else {
            if (!str_contains($to, '/')) {
                $to = util_names::controller_name($this::class) . "/$to";
            }
        }
        return $this->router->url_for($to, $parms);
    }

    function redirect($to = "", $parms = []) {
        #	return $to;
        if ($to && $to != "/" && $to[0] == "/") $url = $to;
        else $url = $this->url($to, $parms);
        util_http::redirect($url);
    }

    // TODO: do we need it? does it work? how to?
    function redirect_referer() {
        $to = $_SERVER['HTTP_REFERER'];
        util_http::redirect($to);
    }
}
