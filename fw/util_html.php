<?php

namespace xorc;

use Laminas\Escaper\Escaper;

class util_html {

    public static $escaper;

    public static function escape_attribute($attr) {
        if (!self::$escaper) self::load_escaper();
        return self::$escaper->escapeHtmlAttr($attr);
    }

    public static function escape_html($html) {
        if (!self::$escaper) self::load_escaper();
        return self::$escaper->escapeHtml($html);
    }

    public static function load_escaper() {
        self::$escaper = new Escaper('utf-8');
    }
}
