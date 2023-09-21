<?php
/*
https://stackoverflow.com/questions/32389009/pdo-with-extended-pdostatement-cannot-disconnect-the-connection-when-set-null
*/

namespace xorc\db;

class pdox_statement extends \PDOStatement {

    protected function __construct(public \PDO $__dbh) {
    }
}
