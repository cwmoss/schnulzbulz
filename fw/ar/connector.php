<?php

namespace xorc\ar;

class connector {
    public static $c = [];

    function __construct(public $db, public $name = '_db', public array $opts = []) {
        if (!$name) $name = '_db';
        $this->name = $name;
        self::add_connection($name, $this);
    }

    function get_prefix() {
        return $this->opts['prefix'] ?? '';
    }

    public static function add_connection($name, $connection) {
        self::$c[$name] = $connection;
    }

    // get the PDO or pdox database
    public static function get($name = '_db') {
        return self::$c[$name];
    }

    public static function  reset() {
        self::$c = [];
    }
}
