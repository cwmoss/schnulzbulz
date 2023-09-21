<?php

namespace xorc\legacy;

class nostore {

	var $validation = null;
	var $errors = null;

	function __construct($parms = null) {
		$this->errors = new errors;


		if (!is_null($parms)) $this->set($parms);
	}

	function set($parms, $allow = null) {
		#   var_dump( $parms);
		if (!is_null($allow)) {
			$allow_check = true;
			if (is_string($allow)) {
				$allow = explode(' ', $allow);
			}
		} else {
			$allow_check = false;
		}
		if ($parms) foreach ($parms as $k => $v) {
			if ($k[0] == '_' || $k == 'errors' || $k == 'validation') continue;
			if ($allow_check && !in_array($k, $allow)) continue;
			$this->$k = $v;
		}
	}

	function set_only_predefined($parms, $allow = null) {
		if (!is_null($allow)) {
			$allow_check = true;
			if (is_string($allow)) {
				$allow = explode(' ', $allow);
			}
		} else {
			$allow_check = false;
		}
		if ($parms) foreach ($parms as $k => $v) {
			if ($k[0] == '_' || $k == 'errors' || $k == 'validation') continue;
			if ($allow_check && !in_array($k, $allow)) continue;
			if (property_exists($this, $k)) $this->$k = $v;
		}
	}

	function get() {
		$arr = array();
		foreach ($this as $k => $v) {
			$arr[$k] = $v;
		}
		return $arr;
	}
	//clear war = true, warum???
	function is_valid($ev = "save", $clear = true) {
		# custom validation event
		if (!is_null($ev)) return $this->validation->validate_event($ev, $this, $clear);

		# cre/up/save
		$ok1 = $this->validation->validate_event("save", $this, $clear);
		return $ok1;
	}

	function __wakeup() {
		$this->errors = new errors;
	}

	function __get($prop) {
		#   print "GET $prop";
		#   print xorcstore_reflection::column_exists($this, $prop);
		#   print_r(xorcstore_reflection::$r);
		#log_error("[AR] magick GET $prop");
		if (method_exists($this, "get_" . $prop)) {
			#	      print "CALLING GET "."get_".$prop;
			return call_user_func(array($this, "get_" . $prop)); # return call_user_method("get_".$prop, $this);

		}
	}
}
