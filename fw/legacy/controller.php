<?php

namespace xorc\legacy;

use xorc\config;
use xorc\request;
use xorc\util;
use xorc\util_http;
use xorc\controller\routable;
use xorc\router;
use xorc\util_names;

class controller {
    use routable;

    var $_layout = array("top" => 1, "bottom" => 1, "page" => 1);
    var $_layout_off = 0;
    var $_layoutname = "";
    var $_layoutpath = "";
    var $_theme;

    var $_post = array(); // postfilter
    var $_post_render = "";

    /*
	   funktionen die mit _unterstrich beginnen können nicht als actions aufgerufen werden
	   welche zusätzlichen funktionen dürfen nicht als actions aufgerufen werden?
	   namen mit leerzeichen trennen
	*/
    var $_disabled_actions = null;
    var $_enabled_actions = null;


    public $before_filter = [];
    public $content;
    public $layout;
    public $title;
    public $navpoint;

    public $_current_action = "";
    public $r;

    function __construct(public config $config, public router $router) {
        if (!$this->title) $this->title = util_names::controller_name(static::class);
        if (!$this->navpoint) $this->navpoint = util_names::controller_name(static::class);
        if ($this->layout) $this->layout($this->layout);

        if (method_exists($this, 'after_construct')) {
            $this->after_construct();
        }
    }
    /*
        request nicht im constructor
        slowly migrate to receiving the request object in the action
        goal: have stateless controller
    */
    function set_request($r) {
        $this->r = $r;
    }

    function _init_before($action, $full_action) {
        $this->_current_action = $full_action;
        if ($this->before_filter) {
            foreach ($this->before_filter as $f) {
                $this->$f();
            }
        }
        return true;
    }

    function _init() {
        return;        // you could init some forms here
    }
    function after_construct() {
        return;
    }


    function layout($l = -1) {
        if ($l != -1) $this->_layoutname = $l;
        return $this->_layoutname;
    }

    function layout_path($path = -1) {
        if ($path != -1) {
            #	      log_error("### SETTING layoutpath {$this->_layoutpath} ==> $path ##");
            $this->_layoutpath = $path;
        }
        return $this->_layoutpath;
    }

    function layout_off() {
        $this->_layout_off = 1;
    }

    function auto($layout) {
        if ($this->_layout_off) return false;
        return $this->_layout[$layout];
    }

    function auto_off($layout_part = null) {
        if ($layout_part) $this->_layout[$layout_part] = null;
        else $this->_layout_off = 1;

        // page abschalten???? muss noch überprüft werden!
    }

    function theme($theme = -1) {
        if ($theme != -1) {
            $this->_theme = $theme;
            // log_error("#### THEME SET TO #$theme#");
        }
        return $this->_theme;
    }

    function _check_if_action_is_allowed($action) {
        $action = strtolower($action);
        if (!is_null($this->_enabled_actions)) {
            return in_array($action, explode(' ', $this->_enabled_actions));
        } else {
            # return true;
            return !in_array($action, explode(
                ' ',
                'link url redirect foreward render start_auth send_file redirect_referer ' .
                    'layout layout_path layout_off auto auto_off theme require_auth'
            ));
        }
    }
}
