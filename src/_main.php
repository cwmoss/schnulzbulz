<?php

use DI\ContainerBuilder;
use FastRoute\RouteCollector;
use app\controller;
use xorc\legacy\dispatcher;
use xorc\router;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use WoohooLabs\Harmony\Harmony;
use WoohooLabs\Harmony\Middleware\DispatcherMiddleware;
use WoohooLabs\Harmony\Middleware\FastRouteMiddleware;
use WoohooLabs\Harmony\Middleware\LaminasEmitterMiddleware;
use xorc\middleware\cors;
use xorc\middleware\fastroute_dispatcher;

/*

https://github.com/nikic/FastRoute/issues/110
TODO: BASEURL
*/

$_SERVER += getenv();
define('XORC_APP_BASE', dirname(__DIR__));
define('XORC_APP_BASEURL', '/');

define('ODA_CLIENT_CONF', dirname(__DIR__) . '/config/' . $_SERVER['ODA_CLIENT'] . '/');

$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions([
    'appname' => 'disco',
    'base' => dirname(__DIR__)
]);
$containerBuilder->addDefinitions(__DIR__ . '/_config.php');
$container = $containerBuilder->build();

$config = $container->get(xorc\config::class);
$config->setup();
xorc\util::set_state('ini', $config->conf);

// session_start();

new xorc\ar\connector($container->get(\xorc\db\pdox::class));
dbg("+++ db", $container->get(\xorc\db\pdox::class));

#dd($container);
$router = $container->get(router::class);
$router->add('GET', '/', [controller\api::class, 'hello']);
$router->add('GET', '/admin/{controller}/{action}[/{id}]', [null, null, 'admin']);
$router->add('POST', '/admin/{controller}/{action}[/{id}]', [null, null, 'admin']);
$router->add('GET', '/{controller}/{action}[/{id}]', null);
$router->add('POST', '/{controller}/{action}[/{id}]', null);

$fr_dispatcher = FastRoute\simpleDispatcher([$router, 'collect_for_fastroute']);
// dd($dispatcher);



$harmony = new Harmony(ServerRequestFactory::fromGlobals(), new Response());
$harmony
    ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()))
    ->addMiddleware(new cors())
    // ->addMiddleware(new FastRouteMiddleware($fr_dispatcher))
    ->addMiddleware(new fastroute_dispatcher($container, $router, $fr_dispatcher))
    ->run();

/*
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);
dbg("++ FROUTE +++", $route);

$container->call([dispatcher::class, 'dispatch_fastroute'], ['route' => $route]);
*/