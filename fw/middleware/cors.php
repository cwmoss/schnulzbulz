<?php

namespace xorc\middleware;

use Psr\Http\Message\ServerRequestInterface as R;
use Psr\Http\Message\ResponseInterface as P;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\EmptyResponse;

class cors implements MiddlewareInterface {

    public $allowed = [
        'http://localhost',
        'http://127.0.0.1',
        'http://0.0.0.0'
    ];

    function __construct() {
    }

    // public function __invoke(R $request, callable $next) {
    public function process(R $request, RequestHandlerInterface $handler): P {
        $origin = $request->getHeaderLine('origin');

        dbg('cors MW', $request->getMethod(), $origin);

        if ($request->getMethod() == 'OPTIONS') {
            $response = new EmptyResponse(204, ["Access-Control-Allow-Methods" => "GET, POST, OPTIONS"]);
            $response = $this->add_headers($origin, $response);
            return $response;
        }
        if ($request->getMethod() == 'HEAD') {
            $response = new EmptyResponse();
            if ($origin) {
                foreach ($this->allowed as $ok) {
                    if (str_starts_with($origin, $ok)) {
                        // $rsp = $next($request);
                        // $rsp = $handler->handle($request);
                        $response = $this->add_headers($origin, $response);
                        // return $this->cors_response($origin);
                    }
                }
            }
            return $response;
        }
        $response = $handler->handle($request);
        $response = $this->add_headers($origin, $response);
        return $response;
    }

    public function add_headers($origin, $rsp) {
        dbg("+++ adding cors headers");
        $hdrs = [
            'Access-Control-Allow-Origin' => "*",
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' =>
            'Content-Type, Authorization, Access-Control-Allow-Origin'
        ];
        foreach ($hdrs as $h => $val) {
            $rsp = $rsp->withHeader($h, $val);
        }
        return $rsp;
    }
}
