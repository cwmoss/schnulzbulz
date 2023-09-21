<?php
// Copyright Monwoo 2017, service@monwoo.com
// Enabling CORS in bultin dev to test locally with multiples servers
// used to replace lack of .htaccess support inside php builting webserver.
// call with :
// php -S localhost:8888 -t webhook webhook/dev-routings.php
$CORS_ORIGIN_ALLOWED = "*"; # "http://localhost:3000";  // or '*' for all   

function consoleLog($level, $msg) {
    file_put_contents("php://stdout", "[" . $level . "] " . $msg . "\n");
}

function applyCorsHeaders($origin) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
}

if (preg_match('/\.(?:png|jpg|jpeg|gif|csv|ico)$/', $_SERVER["REQUEST_URI"])) {
    consoleLog('info', "Transparent routing for : " . $_SERVER["REQUEST_URI"]);
    return false;
}
if (preg_match('/\.(?:css|js)$/', $_SERVER["REQUEST_URI"])) {
    applyCorsHeaders($CORS_ORIGIN_ALLOWED);
    $filePath = "{$_SERVER['DOCUMENT_ROOT']}/{$_SERVER["REQUEST_URI"]}";
    // consoleLog('info', "CORS added to file: {$filePath}");
    consoleLog('info', "HALF-Transparent routing for : " . $_SERVER["REQUEST_URI"]);
    if (!file_exists($filePath)) {
        consoleLog('info', "File not found Error for : " . $_SERVER["REQUEST_URI"]);
        // return false;
        http_response_code(404);
        echo "File not Found : {$filePath}";
        return true;
    }
    $mime = mime_content_type($filePath);
    // https://stackoverflow.com/questions/45179337/mime-content-type-returning-text-plain-for-css-and-js-files-only
    // https://stackoverflow.com/questions/7236191/how-to-create-a-custom-magic-file-database
    // Otherwise, you can use custom rules :
    $customMappings = [
        'js' => 'text/javascript', //'application/javascript',
        'css' => 'text/css',
    ];
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    // consoleLog('Debug', $ext);
    if (array_key_exists($ext, $customMappings)) {
        $mime = $customMappings[$ext];
    }
    consoleLog('info', "CORS added to file {$mime} : {$filePath}");
    header("Content-type: {$mime}");
    echo file_get_contents($filePath);
    return true;
} else {
    // muss sein, sonst klappt die url-base bestimmung nicht
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require_once __DIR__ . '/public/index.php';
}



/*
else if (preg_match('/^.*$/i', $_SERVER["REQUEST_URI"])) {
    $filePath = "{$_SERVER['DOCUMENT_ROOT']}/{$_SERVER["REQUEST_URI"]}";
    applyCorsHeaders($CORS_ORIGIN_ALLOWED);

    if (!file_exists($filePath)) {
        consoleLog('info', "File not found Error for : " . $_SERVER["REQUEST_URI"]);
        // return false;
        http_response_code(404);
        echo "File not Found : {$filePath}";
        return true;
    }
    $mime = mime_content_type($filePath);
    // https://stackoverflow.com/questions/45179337/mime-content-type-returning-text-plain-for-css-and-js-files-only
    // https://stackoverflow.com/questions/7236191/how-to-create-a-custom-magic-file-database
    // Otherwise, you can use custom rules :
    $customMappings = [
        'js' => 'text/javascript', //'application/javascript',
        'css' => 'text/css',
    ];
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    // consoleLog('Debug', $ext);
    if (array_key_exists($ext, $customMappings)) {
        $mime = $customMappings[$ext];
    }
    consoleLog('info', "CORS added to file {$mime} : {$filePath}");
    header("Content-type: {$mime}");
    echo file_get_contents($filePath);
    return true;
} else {
    consoleLog('info', "Not catched by routing, Transparent serving for : "
        . $_SERVER["REQUEST_URI"]);
    return false; // Let php bultin server serve
}
*/