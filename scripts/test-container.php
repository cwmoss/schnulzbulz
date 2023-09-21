<?php


class _aaa {
    function __construct() {
        print "hi!!!";
    }
}

new _aaa;

$logger = $container->get(oda\model\request_logger::class);
$logger->info("data", ['haha' => 'log1']);

$logger2 = $container->get(oda\model\request_logger::class);

$logger2->info("data", ['huhu' => 'log2']);
