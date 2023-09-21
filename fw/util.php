<?php

namespace xorc;

use xorc\dotdata;

class util {

    static public dotdata $data;

    static function set_state($key, $value) {
        if (!isset(self::$data)) self::$data = new dotdata([]);
        self::$data->set($key, $value);
    }

    static function get_state(string|array $path, string $start = null, $default = null) {
        if (!isset(self::$data)) self::$data = new dotdata([]);
        return self::$data->get($path, $start, $default);
    }

    static function url_for($to, $params = []) {
        $fragment = isset($params['#']) ? ('#' . urlencode($params['#'])) : '';
        $host = $params['+'] ?? '';
        unset($params['+'], $params['#']);
        $query = $params ? '?' . http_build_query($params, "", ini_get('arg_separator.output')) : "";
        $to = XORC_APP_BASEURL . $to;
        $to = str_replace('//', '/', $to);
        $url = $to . $query . $fragment;
        return $url;
    }
}
