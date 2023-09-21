<?php

namespace xorc;

use xorc\request as XorcRequest;

class request implements \ArrayAccess, \Iterator {

    public $_data = [];
    public $ctrl;
    public $act;

    private array $keys;
    private $position;

    public function __construct() {
        $this->set_data($_GET + $_POST);
    }

    public function set_data($data) {
        $this->_data = $data;
        $this->keys = array_keys($this->_data);
        $this->position = 0;
    }
    public function set_action($ctrl, $act) {
        $this->ctrl = $ctrl;
        $this->act = $act;
    }

    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset): void {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset): mixed {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    public function rewind(): void {  //Necessary for the Iterator interface. $this->position shows where we are in our list of
        $this->position = 0;      //keys. Remember we want everything done via $this->keys to handle associative arrays.
    }

    public function current(): mixed { //Necessary for the Iterator interface.
        return $this->_data[$this->keys[$this->position]];
    }

    public function key(): mixed { //Necessary for the Iterator interface.
        return $this->keys[$this->position];
    }

    public function next(): void { //Necessary for the Iterator interface.
        ++$this->position;
    }

    public function valid(): bool { //Necessary for the Iterator interface.
        return isset($this->keys[$this->position]);
    }
}

/*
$r = new request;
$r->_data = ['is-null' => null, 'null' => 0, 'width' => 200, 'wh' => ['100', '110']];

var_dump($r['nicht']);
var_dump($r['is-null']);
var_dump($r['wh'][0]);
var_dump(isset($r['nicht-da']));
var_dump(isset($r['is-null']));
var_dump(isset($r['null']));

$a = null;

var_dump(isset($a));
*/