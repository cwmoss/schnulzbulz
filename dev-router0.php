<?php
// dbg2("script-name", $_SERVER['REQUEST_URI'], $_SERVER["SCRIPT_NAME"]);
if ($_SERVER['REQUEST_URI'] == '/favicon.ico') {
    return false;
}
if (preg_match('~\.[^./]+$~', $_SERVER["SCRIPT_NAME"])) {
    $test = $_SERVER["SCRIPT_FILENAME"];
    // dbg2('found .', $_SERVER['SCRIPT_NAME']);
    //    dbg2('found .', $_SERVER);
    //    dbg2('test', $test);

    if ($test != 'dev-router.php') {
        return false;
    }
}

// dbg2('routing-yes', $_SERVER['REQUEST_URI']);


function dbg2($message, $data = []) {
    file_put_contents("php://stdout", $message . ': ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// muss sein, sonst klappt die url-base bestimmung nicht
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/public/index.php';

/*
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|svg|woff|woff2|ttf|ico)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}
*/
