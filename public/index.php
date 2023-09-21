<?php
require __DIR__ . '/../vendor/autoload.php';
require(__DIR__ . '/../src/_main.php');

exit;
$appname = "oda";
$mypath = dirname(dirname(realpath(__FILE__)));

ini_set("include_path", "." . PATH_SEPARATOR . $mypath . "/lib" .
   PATH_SEPARATOR . ini_get("include_path"));

// phpinfo();

// define('XORCAPP_NODISPATCH', true);

//ini_set('display_errors', 1);
// error_reporting(E_ALL);


// cli-server
if (PHP_SAPI == 'cli-server') {
   $_SERVER = array_merge($_SERVER, getenv());
}
# print_r($_SERVER);

include("xorc/xorcapp.class.php");
include("oda/oda.class.php");

$conf = $_SERVER["ODA_CONF"];

# CREDIT_CONF=credit_local.ini php -S localhost:9999 -t public/
if (!$conf) $conf = getenv('ODA_CONF');

if (!$conf) {
   if ($_SERVER["XORC_ENV"] == "development") {
      $conf = $mypath . "/config/{appname}_dev.ini";
   } else {
      $conf = $mypath . "/config/{appname}_prod.ini";
   }
} elseif ($conf[0] != '/') {
   $conf = $mypath . "/config/" . $conf;
}

$_SERVER["ODA_CONF"] = $conf;

#print_r($_SERVER);

/*
kleiner hack, weil wir in xorc1 noch keine dynamischen routen haben
 */
$oda = XorcApp::run("oda");

//if(isset($_SERVER["BOB_HOMEPAGE"])){
//   $bob->router->map['/'] = $_SERVER["BOB_HOMEPAGE"];
//}
//bob_logger::start_time();
// $bob->dispatch();
//bob_logger::finish();
