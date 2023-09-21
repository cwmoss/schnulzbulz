<?php

namespace xorc;

class dotdata {

    function __construct(
        public array|object $data
    ) {
    }

    // TODO: let's use a real path
    function set($start, $data) {
        $this->data[$start] = $data;
    }

    function get($path, $start = null, $default = null) {

        if (!is_array($path)) {
            $path = explode('.', $path);
        }

        if ($start) {
            array_unshift($path, $start);
        }
        $current = $this->data;
        $current_path = [];

        foreach ($path as $part) {
            $current_path[] = $part;

            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } elseif (is_object($current) && isset($current->$part)) {
                $current = $current->$part;
            } else {
                return $default;
            }
        }
        return $current;
    }

    function get_call($path, $start = null) {
        $val = $this->get($path, $start);
        if (is_callable($val)) {
            $val = $val();
        }
        return $val;
    }

    /*
        adress.plz, plz2 => address.plz2
    */
    function path_update($current_path, $new_end) {
        $path = explode('.', $current_path);
        $path[array_key_last($path)] = $new_end;
        return join('.', $path);
    }

    function path_suffix($current_path, $suffix) {
        return $current_path . $suffix;
    }
}
