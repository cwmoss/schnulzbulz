<?php

namespace xorc\db;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class logger extends AbstractLogger {

    function __construct(public $minlevel = LogLevel::DEBUG) {
    }

    public function log($level, $message, array $context = []): bool {


        //print $message . \PHP_EOL;
        //print_r($context);
        if (is_string($context)) $context = str_replace(["\n", "\r"], "", $context);
        error_log(json_encode([$message, $context], \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
        return false; // init() failed.
    }
}
