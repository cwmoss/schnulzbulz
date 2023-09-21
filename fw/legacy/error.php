<?php

namespace xorc\legacy;

class error {

    var $col;
    var $msg;
    function __construct($col, $msg) {
        $this->col = $col;
        $this->msg = $msg;
    }
}
