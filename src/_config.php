<?php

use Psr\Container\ContainerInterface;
use DI\Factory\RequestedEntry;

use function DI\factory;
use function DI\create;
use function DI\get;

return [
    xorc\config::class => DI\create()
        ->constructor(DI\get('base'), DI\get('appname'), DI\get('configfile')),
    xorc\db\pdox::class => DI\create()
        ->constructor('sqlite:' . XORC_APP_BASE . '/disco.db', null, null, new xorc\db\logger),

    'soap_wsdl' => function (ContainerInterface $c) {
        //dbg("+++ show env", $c->get('env'));
        return $c->get(xorc\config::class)->conf['bob']['wsdl'];
        // $wsdl = $c->get('env')['WSDL'] ?? null;

        //if (!$wsdl && $c->get('conf')['dev']) {
        //    $wsdl = $c->get('appbase') . "/service.wsdl";
        //}
        // return $wsdl;
    },
    oda\soap\service::class => DI\create()
        //    ->constructor(get(bob\logger::class), get(bob\auth::class), get('soap_wsdl'), null, get('soap_sessionhack'), get('soap_flavour')),
        ->constructor(get('soap_wsdl'), null, get('soap_sessionhack'), get('soap_flavour')),
    /*     
    twentyseconds\validation\validator::class => function (ContainerInterface $c) {
        $content = file_get_contents(__DIR__ . '/../config/validations.yaml');
        $content = text_for($content, ['diacritical' => BOB_DIA_OK]);
        $parsed = \Symfony\Component\Yaml\Yaml::parse($content, \Symfony\Component\Yaml\Yaml::PARSE_CUSTOM_TAGS);
        twentyseconds\validation\validator::get_providers();
        twentyseconds\validation\validator::add_provider(new bob\validationsprovider($c->get(bob\soap\service::class), $c->get(twentyseconds\plzcheck::class)));
        return new twentyseconds\validation\validator($parsed, ['domain' => $c->get(cwmoss\doda::class)]);
    },
    */
    disco\request_logger::class => function () {
        // dbg("+++ create logger");
        $conf = get(xorc\config::class);
        $name = xorc_ini('request_log.name');
        if (!$name) $name = "request";
        $file = xorc_ini('request_log.file');
        if (!$file) $file = "requests.log";
        $file = XORC_APP_BASE . '/var/' . $file;
        // achtung cli!
        $level = null;
        if (!$level) $level = "info";

        $formatter = new \Monolog\Formatter\LogstashFormatter($name);
        $logger = new oda\model\request_logger($name);
        if ($level == 'never') {
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
        } else {
            $info = new Monolog\Handler\StreamHandler($file, Monolog\Logger::INFO);
            $info->setFormatter($formatter);
            $logger->pushHandler($info);
            $collector = new oda\model\collecting_data_handler();
            $logger->pushHandler($collector);
            $logger->pushProcessor($collector);
        }
        register_shutdown_function(function () use ($logger) {
            $logger->info("finished");
        });
        // dd($logger);
        return $logger;
    }
    /*
          $this->request_log = setup_request_logger(xorc_ini('request_log.name'), xorc_ini('request_log.file'), $loglevel);

      
      */
];
