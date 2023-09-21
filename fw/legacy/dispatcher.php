<?php

namespace xorc\legacy;

use DI\Container;
use FastRoute\Dispatcher as fr_dispatcher;
use xorc\util;
use xorc\request;
use Psr\Http\Message\ResponseInterface;
use xorc\util_names;

class dispatcher {

    public function __construct(public Container $container) {
    }

    public function dispatch_fastroute(array $route, $psr_request) {
        [$route, $callable_props, $params] = $route + [2 => []];

        $callable_props[0] ??= $params['controller'];
        $callable_props[1] ??= $params['action'];
        if (isset($params['prefix'])) {
            $callable_props[2] ??= $params['prefix'];
        }
        $params['psr7'] = $psr_request;

        # dd($callable);
        return match ($route) {
            fr_dispatcher::NOT_FOUND => throw new \Exception("Not Found"),
            fr_dispatcher::METHOD_NOT_ALLOWED => throw new \Exception("Method Not Allowed"),
            fr_dispatcher::FOUND => $this->dispatch_route($callable_props, $params),
        };
    }

    public function dispatch_route(array $callable_props, array $parameters) {
        dbg("dispatch-route", $callable_props);
        [$controller, $action, $prefix] = $callable_props;
        // check access
        if ($controller[0] == '_' || $action[0] == '_') throw new \Exception("Not Found");

        $classname = util_names::controller_classname_from_router_props($controller, $prefix);
        $ctrl = $this->container->make($classname);

        $request = $this->container->make(request::class);
        $request->set_action(util_names::controller_name($ctrl::class), $action);

        // der legacy request kann in der action konsumiert werden
        $parameters['request'] = $request;

        if (method_exists($ctrl, 'set_request')) $ctrl->set_request($request);
        if (method_exists($ctrl, '_init_before')) $ctrl->_init_before($action, $controller . '/' . $action);
        if (method_exists($ctrl, '_init')) $ctrl->_init();

        util::set_state('ctrl', $ctrl);

        $result = $this->container->call([$ctrl, $action], $parameters);
        return $this->handle_result($result, $ctrl, $action);
    }

    public function handle_result($res, $ctrl, $action) {
        $theme = (method_exists($ctrl, 'theme')) ? $ctrl->theme() : '';
        if ($res instanceof ResponseInterface) {
            // dd("yes");
            return $res;
        } elseif($res instanceof view){
            $view->set_theme_base($theme, $this->container->get('base');
        }elseif ($res === null) {
            $view = new view($ctrl, $action);
            util::set_state('view', $view);
        } elseif (is_string($res)) {
            $view = new view($ctrl, $res, $theme, $this->container->get('base'));
            util::set_state('view', $view);
        } elseif ($res === false) {
            // do nothing
            dbg("+++ result: nothing");
            return;
        }
        // var_dump($res, $view);
        $out = $view->render();
        $html = $view->render_page($out);
        if ($ctrl->_post_render) {
            $html = [$ctrl, $ctrl->_post_render](...)($html);
        }
        return $html;
        // dd($ctrl);
    }
}
