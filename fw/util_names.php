<?php

namespace xorc;

class util_names {

    static function controller_name($object_or_string) {
        if (is_object($object_or_string)) $object_or_string = $object_or_string::class;
        [$app, $name] = explode('\\controller\\', $object_or_string, 2) + [null, ''];
        return str_replace('\\', '.', $name);
    }

    // ex. "dashboard", "admin" => app\controller\admin\dashboard
    static function controller_classname_from_router_props($controller, $prefix, $appname = "app") {
        if ($prefix) {
            $prefix = str_replace('.', '\\', $prefix) . '\\';
        }
        return $appname . '\\controller\\' . $prefix . $controller;
    }

    // TODO: remove
    static function class_name_to_controller_name($cls) {
        $name = is_object($cls) ? $cls::class : $cls;
        $parts = explode('\\controller\\', $name);
        return str_replace('\\', '/', $parts[1]);
    }

    static function class_basename($object_or_string) {
        if (is_object($object_or_string)) $object_or_string = $object_or_string::class;
        $name = strtolower($object_or_string);
        $name = substr(strrchr($name, '\\'), 1);
        return $name;
    }
}
