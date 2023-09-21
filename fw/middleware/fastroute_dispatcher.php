<?php

namespace xorc\middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use xorc\dispatcher;


/*
https://docs.laminas.dev/laminas-diactoros/v3/custom-responses/
*/

class fastroute_dispatcher implements MiddlewareInterface {

    function __construct(public $container, public $router, public $fr_dispatcher) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $route = $this->fr_dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        dbg("+++ routing to ", $route);
        $resp = $this->container->call([dispatcher::class, 'dispatch_fastroute'], ['route' => $route, 'psr_request' => $request]);
        dbg("+++ dispatcher response", $resp);
        if ($resp instanceof ResponseInterface) {
            return $resp;
        }

        $response = new HtmlResponse($resp);
        return $response;
    }
}
