<?php

namespace xorc;

require_once('legacy/formtag_helper.php');
/*
TODO: layout definition
*/
class view {

    public array $auto = array("top", "bottom");
    public string $prefix;
    public string $ctrl_name;
    public object $controller;
    public string $action;
    public string $theme;
    public string $base;
    public string $layout = "";

    function __construct(public string $name, public array $data = []) {
    }

    static function new_from_controller(object $controller, string $action): view {
        [$app, $name] = explode('\\controller\\', $controller::class, 2);
        $parts  = explode('\\', $name, 2);
        if (isset($parts[1])) {
            $prefix = $parts[0];
            $ctrl_name = $parts[1];
        } else {
            $prefix = "";
            $ctrl_name = $parts[0];
        }
        $name = strtolower($ctrl_name . "_" . $action);
        if ($prefix) {
            $name = $prefix . '/' . $name;
        }
        $v = new self($name);
        $v->setup_ca($controller, $action, $prefix, $ctrl_name);
        return $v;
    }

    function setup($base, $theme = ""): view {
        $this->base = $base;
        $this->theme = $theme;
        return $this;
    }

    function setup_ca(object $controller,  string $action,  string $prefix,  string $ctrl_name): view {
        $this->controller = $controller;
        $this->action = $action;
        $this->prefix = $prefix;
        $this->ctrl_name = $ctrl_name;
        return $this;
    }

    function set_layout($layout): view {
        $this->layout = $layout;
        return $this;
    }

    function data(array $data): view {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    function render($view = "", $data = []) {
        $view = strtolower($view);
        // XorcApp::$inst->log("VIEW $view");
        if (!$view) $view = strtolower($this->name);
        elseif ($view[0] == "/") $view = substr($view, 1);
        $view .= ".html";

        $out = $this->_include($view, $data, 1);
        return $out;
    }

    function render_part($view = "", $data = array()) {
        // XorcApp::$inst->log("RND PART $view");
        $inc = $this->find_partial($view);
        if (!$inc) return "NOT FOUND: $view";
        $out = $this->_include($inc, $data, 0);
        return $out;
    }

    function find_partial($view) {
        $file = basename($view);
        # evtl. enthält der view ein unterverzeichnis, 
        #     dann muss der dateiname neu gebaut werden
        #     in diesem fall geschieht die adressierung
        #     *immmer* relativ zum view/[theme] verzeichnis
        #     controller paths werden dann *nicht* mehr 
        #     berücksichtigt
        $direct = ($file != $view);
        if ($direct) {
            $viewfile = dirname($view) . "/_" . $file . ".html";
            // log_error("--- file != view");
        } else {
            $viewfile = "_" . $file . ".html";
        }

        # controllerpath (enthält "/" zb. "admin/")
        $path = $this->prefix;
        # theme

        $theme = $this->theme;
        $found = false;

        if ($theme) {
            $base = $this->base . "/themes/$theme";

            if ($direct) {
                $check = "$base/view/$viewfile";
            } else {
                $check = "$base/view/$path/$viewfile";
            }

            if (file_exists($check)) {
                $found = $check;
            } elseif (!$direct && $path) {
                # ohne pfad ein verzeichnis nach oben testen
                $check = "$base/view/$viewfile";
                if (file_exists($check)) {
                    $found = $check;
                }
            }
        }

        if (!$found) {
            $base = $this->base;
            if ($direct) {
                $check = "$base/view/$viewfile";
            } else {
                $check = "$base/view/$path/$viewfile";
            }

            if (file_exists($check)) {
                $found = $check;
            } elseif (!$direct && $path) {
                # ohne pfad ein verzeichnis nach oben testen
                $check = "$base/view/$viewfile";
                if (file_exists($check)) {
                    $found = $check;
                }
            }
        }

        return $found;
    }

    function render_page($content = "") {
        #	   log_error("BASE:".XorcApp::$inst->base);
        $c = $this->controller;
        $charset = "UTF-8";
        // header("Content-type: text/html; charset=$charset");
        // print "RENDER PAGE "; print_r($c);
        $layout = $this->layout;
        if ($layout) $layout = "_$layout";

        $path = "";
        $base = $this->base;
        $theme = $this->theme;
        if ($theme) $base .= "/themes/$theme/view";
        else $base .= "/view";
        if ($path) {
            $path = $base . "/" . $path;
        } else {
            $path = $base;
        }
        // log_error("THEME  $theme #");
        # log_error($c);
        // log_error("LAYOUT $path _ $layout #");
        // log_error("PAGE LAYOUT: " . $path . "/_layout{$layout}.page.html");
        $buffer = array();

        $c->layout = $buffer;
        dbg("layout?", $path . "/_layout{$layout}.page.html");

        if ($c->auto_layout && file_exists($path . "/_layout{$layout}.page.html")) {
            #  log_error($c->layout);
            return $this->_include($path . "/_layout{$layout}.page.html", ['content' => $content]);
        } else {
            return $content;
        }
    }

    function _include($file, $_original_params = array(), $rel = 0) {
        dbg("view, orig params", $file, $_original_params, $this->base);
        if (is_null($_original_params)) throw new \Exception('null params');

        $theme = $this->theme;
        if ($theme && ($rel || $file[0] != "/")) {
            $file0 = $this->base . "/themes/$theme/view/$file";
            // log_error("theme view $file0 ?");
            if (file_exists($file0)) {
                // log_error("OK.");
                $file = $file0;
            } else {
                // log_error("FAILED.");
                $theme = null;
            }
        }
        if (!$theme) {
            if ($rel || $file[0] != "/") $file = $this->base . "/view/$file";
        }
        // log_error("view $file");
        if (!file_exists($file)) {
            // log_error("!!! missing VIEW $file");
            return "";
        }
        $H = $this->controller;
        foreach ($this->data as $key => $val) {
            if (!isset($$key)) $$key = $val;
        }
        foreach ($_original_params as $key => $val) {
            $$key = $val;
        }
        dbg("view_include: $file");
        ob_start();
        include($file);
        $out = ob_get_clean();
        dbg("view result:", $out);
        //		print $out;
        // log_error("OK");
        return $out;
    }
}
