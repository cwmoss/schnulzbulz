<?php

namespace xorc;

class flash {

    public $data;

    function __construct() {
        if (!isset($_SESSION['__xorc_flash'])) {
            $_SESSION['__xorc_flash'] = ['msg' => ['', 'info'], 'vars' => []];
        }
        $this->data = $_SESSION['__xorc_flash'];
    }

    function write(string $message, string $type = 'info') {
        $this->data['msg'] = [$message, $type];
        $_SESSION['__xorc_flash'] = $this->data;
    }

    function read() {
        $msg = $this->read_typed();
        if ($msg) {
            return $msg[0];
        }
        return null;
    }

    function read_typed() {
        $msg = $this->data['msg'] ?? null;
        unset($this->data['msg']);
        $_SESSION['__xorc_flash'] = $this->data;
        return $msg;
    }

    function write_var(string $name, $value) {
        $this->data['vars'][$name] = $value;
        $_SESSION['__xorc_flash'] = $this->data;
    }

    function read_var(string $name) {
        $val = $this->data['vars'][$name];
        unset($this->data['name']);
        $_SESSION['__xorc_flash'] = $this->data;
        return $val;
    }
}
