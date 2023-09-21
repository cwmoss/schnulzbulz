<?php

namespace xorc\ar;

use Exception;
use xorc\db\columns;

class tableinfo {

    function __construct(public array $conf, public array $fields) {
        $this->setup();
    }

    function setup() {
        if ($this->type('created_at') == columns::DATETIME) $this->conf['autodate']['created'] = "created_at";
        if ($this->type('modified_at') == columns::DATETIME) $this->conf['autodate']['modified'] = "modified_at";
    }

    function type($col) {
        if (!isset($this->fields[$col])) return null;
        return $this->fields[$col]['type'];
    }

    function fields() {
        return $this->fields;
    }
    function column_names() {
        //print_r($this);
        //die("--xx");
        return array_keys($this->fields);
    }
    function table() {
        return $this->conf['table']['name'];
    }
    function db() {
        return $this->conf['table']['db'];
    }
}
