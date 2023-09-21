<?php

use xorc\util;

function url($to = "", $parms = []) {
    //	print ("URL:$to"); print_r(XorcApp::$inst);
    $ctrl = util::get_state('ctrl');
    if ($ctrl) {
        return $ctrl->url($to, $parms);
    }
    return 'unknown';
    # if (!XorcApp::$inst->ctrl) return XorcApp::$inst->router->url_for($to, $parms);
}

function url_for($to = "", $parms = array()) {
    return XorcApp::$inst->ctrl->url($to, $parms);
}

// unencoded htmlentities, hmm alles falsch
function uurl($to = "", $parms = array()) {
    $u = url($to, $parms);
    return str_replace('&amp;', '&', $u);
}

function selfurl() {
    return XorcApp::$inst->location;
}

function image($url, $parms = array()) {
    //    $url=url(XorcApp::$inst->ctrl->base."/$url", $parms);
    $url = XORC_APP_BASEURL . "/$url";
    $opts = array_merge(array("src" => $url, "border" => 0), $parms);
    return sprintf('<img %s />', opts_to_html($opts));
}

function image_path($url, $opts = null) {
    //    $url=url(XorcApp::$inst->ctrl->base."/$url", $parms);
    if (!$opt) $opts = array();
    if ($opts == "+") $opts = array("+" => "");
    if (isset($opts['+'])) {
        $ph = proto_host($opts['+']);
    } else {
        $ph = "";
    }
    log_error("#### BASE  " . XORC_APP_BASEURL);
    $url = $ph . XORC_APP_BASEURL . "/$url";
    return $url;
}

function proto_host($ph = "") {
    $proto = $host = "";
    if (preg_match("!^(https?://)(.*?)$!", $ph, $phmat)) {
        $proto = $phmat[1];
        $host = $phmat[2];
    } else {
        $host = $ph;
    }
    if (!$host) $host = XorcApp::$inst->env->server;
    if (!$proto) $proto = XorcApp::$inst->env->proto;
    $host = $proto . $host;
    return $host;
}

function kill_chache($anticache = null) {
    if ($anticache === null) {
        if ((@$_ENV["XORC_ENV"] && $_ENV["XORC_ENV"] == "development") || (@$_SERVER["XORC_ENV"] && $_SERVER["XORC_ENV"] == "development")) {
            #if(($_ENV["XORC_ENV"] && $_ENV["XORC_ENV"]=="production") || ($_SERVER["XORC_ENV"] && $_SERVER["XORC_ENV"]=="production")){
            # $nocache="";
            $nocache = "?" . time();
        } else {
            # $nocache="?".time();
            $nocache = "";
        }
    } else {
        if ($anticache) {
            // versionsstrings
            if (is_string($anticache)) {
                $nocache = "?$anticache";
            } else {
                $nocache = "?" . time();
            }
        } else $nocache = "";
    }
    return $nocache;
}

function js_tag($url, $anticache = null) {
    $nocache = kill_chache($anticache);
    $url = XORC_APP_BASEURL . "$url" . $nocache;
    return sprintf('<script type="text/javascript" src="%s"></script>' . "\n", $url);
}

function css_tag($url, $anticache = null) {
    $nocache = kill_chache($anticache);
    $url = XORC_APP_BASEURL . "$url" . $nocache;
    return sprintf('<link rel="stylesheet" href="%s" type="text/css" />' . "\n", $url);
}

function themed_js_tag($url, $anticache = null) {
    $nocache = kill_chache($anticache);
    $th = util::get_state('view')->theme;
    $url = XORC_APP_BASEURL . "themes/$th/$url" . $nocache;
    return sprintf('<script type="text/javascript" src="%s"></script>' . "\n", $url);
}

function themed_css_tag($url, $anticache = null) {
    $nocache = kill_chache($anticache);
    $th = util::get_state('view')->theme;
    $url = XORC_APP_BASEURL . "themes/$th/$url" . $nocache;
    return sprintf('<link rel="stylesheet" href="%s" type="text/css" />' . "\n", $url);
}

function themed_asset($url = null) {
    $th = util::get_state('view')->theme;
    $url = XORC_APP_BASEURL . ($th ? "themes/$th/$url" : "$url");    //ae 100106 (so muss nicht jedes standard-asset in alle themes kopiert werden)
    //$url=XorcApp::$inst->ctrl->base."/themes/$th/$url";  							//original
    return $url;
}

function is_ajax() {
    return Xorcapp::$inst->req->ajax ? true : false;
}

// TODO: implement set
function xorc_ini($key, $set = null) {
    $default = str_contains($key, '.') ? null : [];
    return util::get_state($key, 'ini', $default);
}

function log_error(...$msgs) {
    dbg(...$msgs);
}

function log_db_error($msg, $newline = "\n") {
    XorcApp::$inst->log($msg);
}

function camelcase_to_underscore($name) {
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
}
function underscore_to_camelcase($name, $ucfirst = false) {
    if ($ucfirst) $name = ucfirst($name);

    // 5.3: function($m){return strtoupper($m[1]);}
    $f = function ($m) {
        return strtoupper($m[1]);
    };
    return preg_replace_callback('/_([a-z])/', $f, $name);
}

function remove_bom($str) {
    if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
        $str = substr($str, 3);
    }
    return $str;
}

function render_part($tpl, $parms = array()) {
    return util::get_state('view')->render_part($tpl, $parms);
}



function button_to($to, $text = "", $parms = array(), $htmlparms = array()) {
    if (!$text) $text = $to;
    if (is_null($parms)) $parms = array();

    $method = $htmlparms['method'];
    if (!$method) $method = "post";
    //   if($htmlparms['confirm']) $onsubmit="onsubmit=\"return(confirm('{$htmlparms['confirm']}'))\"";
    if ($htmlparms['confirm']) {
        $onsubmit = 'onsubmit="' . htmlspecialchars("return(confirm('{$htmlparms['confirm']}'))") . '"';
    } elseif ($htmlparms['jq_confirm']) {
        $onsubmit = 'data-confirm="' . htmlspecialchars($htmlparms['jq_confirm']) . '"';
    } else {
        $onsubmit = "";
    }

    $btn_class = $htmlparms['class'];
    if (!$btn_class) $btn_class = "btn";
    $btn_title = $htmlparms['title'];

    $type = $htmlparms['type'] ? $htmlparms['type'] : 'input';
    if ($type == 'button') {
        $button = sprintf(
            '<button type="submit" class="%s" value="%s">%s</button>',
            $btn_class,
            htmlspecialchars($text),
            $btn_title
        );
    } else {
        $button = sprintf(
            '<input type="submit" class="%s" value="%s" title="%s">',
            $btn_class,
            htmlspecialchars($text),
            htmlspecialchars($btn_title)
        );
    }


    $html = sprintf(
        '<form method="%s" action="%s" %s class="button-to">%s',
        $method,
        XorcApp::$inst->ctrl->url($to),
        $onsubmit,
        $button
    );
    if (!is_array($parms) && $parms) {
        $parms = array("id" => $parms);
    }
    foreach ($parms as $p => $val) {
        $html .= hidden_field_tag($p, $val);
    }
    return $html . "</form>";
}

# $this->r['back']||$this->r['back_x']||$this->r['back_y']
function button_pressed($btn) {
    $r = XorcApp::$inst->ctrl->r;
    return ($r[$btn] || $r["{$btn}_x"] || $r["{$btn}_y"]) ? true : false;
}

function link_to($to, $text = "", $parms = array(), $options = array()) {
    $htmloptions = "";
    foreach ($options as $k => $v) {
        $htmloptions .= " $k=\"" . htmlspecialchars($v) . "\"";
    }
    if (!$text) $text = $to;
    if (is_null($parms)) $parms = array();
    return sprintf(
        '<a href="%s"%s>%s</a>',
        XorcApp::$inst->ctrl->url($to, $parms),
        $htmloptions,
        $text
    );
}


function flash($msg = null, $type = null) {
    $flash = new xorc\flash;
    if (!is_null($msg)) {
        $flash->write($msg, $type);
    } else {
        if ($type) {
            return $flash->read_typed();
        } else {
            return $flash->read();
        }
    }
}

function flash_var($key, $val = null) {
    $flash = new xorc\flash;
    if (is_null($val)) {
        return $flash->read_var($key);
    } else {
        $flash->write_var($key, $val);
    }
}



function subaction($cont, $act) {
    return XorcApp::$inst->subaction($cont, $act);
}

function slot($name) {
    return XorcApp::$inst->outv[$name];
}
