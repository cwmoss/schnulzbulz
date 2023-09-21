<?php

namespace xorc\legacy;

require_once('formtag_helper.php');

class view {

	public array $auto = array("top", "bottom");
	public string $prefix;
	public string $ctrl_name;

	function __construct(public controller $controller, public string $action, public string $theme, public string $base) {
		$name = get_class($controller);
		$name = explode('\\controller\\', $name);
		$parts = explode('\\', $name[1], 2);
		if (isset($parts[1])) {
			$this->prefix = $parts[0];
			$this->ctrl_name = $parts[1];
		} else {
			$this->prefix = "";
			$this->ctrl_name = $parts[0];
		}
	}
	function render($view = "", $params = array()) {
		$view = strtolower($view);
		// XorcApp::$inst->log("VIEW $view");
		if (!$view) $view = strtolower($this->ctrl_name . "_" . $this->action);
		elseif ($view[0] == "/") $view = substr($view, 1);
		else $view = strtolower($this->ctrl_name . "_" . $view);
		if (preg_match("!\.(\w+)$!", $view, $m)) {
			if ($m[1] == 'xml') {
				// XorcApp::$inst->nopage = true;
				header("Content-Type: text/xml; charset=UTF-8");
			} elseif ($m[1] == 'js') {
				// XorcApp::$inst->log("JS VIEW");
				// XorcApp::$inst->nopage = true;
				#            header("Content-Type: text/javascript");
			}
		} else {
			$view .= ".html";
		}
		$out = $this->_include($view, $params, 1);

		return $out;
	}

	function render_part($view = "", $params = array()) {
		// XorcApp::$inst->log("RND PART $view");
		$inc = $this->find_partial($view);
		if (!$inc) return "NOT FOUND: $view";
		$out = $this->_include($inc, $params, 0);
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
		header("Content-type: text/html; charset=$charset");
		// print "RENDER PAGE "; print_r($c);
		$layout = $c->layout();
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

		if ($c->auto('page') && file_exists($path . "/_layout{$layout}.page.html")) {
			#  log_error($c->layout);
			return $this->_include($path . "/_layout{$layout}.page.html", ['content' => $content]);
		}
	}

	function _include($file, $_original_params = array(), $rel = 0) {
		dbg("view, orig params", $_original_params);
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
		foreach ($this->controller as $key => $val) {
			if (!isset($$key)) $$key = $val;
		}
		foreach ($_original_params as $key => $val) {
			$$key = $val;
		}
		// log_error("view_include: $file");
		ob_start();
		include($file);
		$out = ob_get_clean();
		//		print $out;
		// log_error("OK");
		return $out;
	}
}
