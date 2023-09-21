<?php

namespace xorc;

use FastRoute\RouteCollector;
use xorc\util;

/*
    TODO: optimize for url creation, basepath, proto-host
*/

class router {

    public array $routes;

    function __construct() {
    }

    function add($method, $path, $handler, $name = "") {
        $this->routes[] = [$method, $path, (array) $handler + [null, null, null], $name];
    }

    function collect_for_fastroute(RouteCollector $r) {
        foreach ($this->routes as $route) {
            $r->addRoute($route[0], $route[1], $route[2]);
        }
    }

    function url_for($path = "", $params = []) {
        // dbg("router: url_for", $path, $params);

        if (!$path) {
            return util::url_for($path, $params);
        }
        if ($path[0] == ':') {
            $route = $this->find_route_by_name($path);
        } else {
            $route = $this->find_route($path, $params);
        }

        return $this->fill_path($route, $params);
    }

    function fill_path($route, $params = []) {
        if (is_string($route)) return $route;
        // dbg_flush('fill-path', $route, $params);
        $params['controller'] = $route[2][0];
        $params['action'] = $route[2][1];
        $params['prefix'] = $route[2][2];
        $previous = $route[1];
        foreach ($params as $k => $v) {
            if (is_array($v)) continue;
            // fragments
            if ($k[0] == '#') continue;
            // proto+host command
            if ($k[0] == '+') continue;
            // numeric keys
            if (is_int($k)) continue;
            $url = strtr($previous, [
                '[/{' . $k . '}]' => '/' . $v,
                '{' . $k . '}' => $v
            ]);
            if ($url != $previous) {
                unset($params[$k]);
                $previous = $url;
            }
        }

        // remove remaining optional parameters
        $url = preg_replace('~\[/\{\w+\}\]~', "", $url);

        if (str_contains($url, '{')) {
            dbg("+++ missing", $url, $params);
            return "route-missing-parameters";
        }
        unset($params['controller'], $params['action'], $params['prefix']);
        return util::url_for($url, $params);
    }

    function find_route($path, $params) {
        [$controller, $action] = explode('/', $path) + [null, null];
        $parts = explode('.', $controller);
        $controller = array_pop($parts);
        if ($parts) {
            $prefix = join('.', $parts);
        } else {
            $prefix = null;
        }
        foreach ($this->routes as $route) {
            [$c, $a, $p] = $route[2];
            dbg("+++ match? ", [$controller, $action, $prefix], [$c, $a, $p]);
            $found_route = match ([$c, $a, $p]) {
                [$controller, $action, $prefix] => true,
                [$controller, $action, null] => true,
                [$controller, null, $prefix] => true,
                [$controller, null, null] => true,
                [null, $action, $prefix] => true,
                [null, $action, null] => true,
                [null, null, $prefix] => true,
                [null, null, null] => true,
                default => false
            };
            if ($found_route) {
                $route[2] = [$controller, $action, $prefix];
                return $route;
            }
        }
        dbg("+unmatched", $path);
        return "route-unmatched";
    }

    function find_route_by_name($name) {
        $name = ltrim($name, ':');
        foreach ($this->routes as $route) {
            if ($route[3] == $name) {
                return $route;
            }
        }
        return "route-unknown-name";
    }
}
