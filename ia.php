<?php

use xorc\db;
use xorc\ar;

require __DIR__ . '/vendor/autoload.php';


$db = new db\pdox("mysql:dbname=oda;host=127.0.0.1;port=3307", "root", "123456", new db\logger);
# $db = new db\pdox("sqlite:testia.db", "root", "123456", new db\logger);
$con = new ar\connector($db);

class article extends ar\base {

    static function define_schema() {
        return ['table' => 'testarticles'];
    }
}
