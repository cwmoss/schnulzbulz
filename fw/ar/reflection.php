<?php

namespace xorc\ar;

use xorc\db\schema;

class reflection {

    static public $r = [];
    static public $db = [];

    static public function  reset() {
        self::$r = [];
        self::$db = [];
    }
    static function get_info($o, $info) {
        $klas = is_string($o) ? $o : $o::class;
        if (!isset(self::$r[$klas])) {
            dbg("+++ INFO FOR ++++ $klas #\n");
            self::fetch_info($klas);
        }
        return self::$r[$klas]->$info();
    }
    static function fetch_info($klas) {
        $conf = [$klas, 'define_schema'](...)();
        if (!isset($conf['keys'])) $conf['keys'] = ["id"];
        $table = ["name" => $conf['table']];
        $con = connector::get($conf['db'] ?? '_db');
        $db = $con->db;
        $prefix = ($con->get_prefix() && !isset($conf['noprefix'])) ?
            $con->get_prefix() . "_" : "";
        $table += [
            'prefix' => $prefix,
            'db' => $conf['db'] ?? '_db',
            'sequence' => $conf['sequence'] ?? null,
            'idfunction' => $conf['idfunction'] ?? null,
            'sti_type' => $conf['sti_type'] ?? null
        ];
        $table['name'] = $prefix . $table['name'];
        $conf['table'] = $table;
        $fields = (new schema($db))->getColumns($table['name']);
        self::$r[$klas] = new tableinfo($conf, $fields);
    }

    static function db($o) {
        return self::get_info($o, 'db');
    }
    static function table($o) {
        return self::get_info($o, 'table');
    }

    static function columns($o) {
        return self::get_info($o, 'fields');
    }
    static function column_names($o) {
        // die("endddd");
        return self::get_info($o, 'column_names');
    }
    static function column_exists($o, $col) {
        return in_array($col, self::get_info($o, 'column_names'));
    }
    static function primary_key($o) {
        return self::$r[$o::class]['keys'][0];
    }

    static function is_primary_key($o, $k) {
        return self::$r[$o::class]['keys'][0] == $k;
    }

    static function assoc($o, $name) {
        return self::$r[$o::class]['relation_names'][$name];
    }

    static function assocs($o) {
        return self::$r[$o::class]['relation_names'];
    }

    static function auto_created($o) {
        if (isset(self::$r[$o::class]['autodate']['created']))
            return self::$r[$o::class]['autodate']['created'];
        return false;
    }
}
