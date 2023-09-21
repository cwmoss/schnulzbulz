<?php

namespace testapp\model;

use xorc\db;
use xorc\ar;
use xorc\ar\base;

class article extends base {
    public $row = 55;

    public function before_save() {
        if ($this->status == 'published' && !$this->published_at) {
            $this->published_at = date('Y-m-d H:i:s');
        }
    }
    static function define_schema() {
        return ['table' => 'testarticles'];
    }
}

class feature extends article {
}

class car extends base {
}
