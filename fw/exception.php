<?php

namespace xorc;

class exception extends \Exception {

    public $opts = array();
    public $trace;
    public $message;

    static $defaults = array(
        "header" => "404",
        /*
      kein spezielles exception layout verwenden
      alternative:
         layout 0 // abschalten
         layout false // abschalten
         layout "error" // eignes error-layout verwenden
         
   */
        "layout" => "",
        #       "theme"=>"",
        "view" => "/errors/default.html"
    );

    function __construct($msg, $opts = array(), $code = 0) {
        log_error("xorcruntime exception");
        parent::__construct($msg, $code);
        $this->opts = $opts;
    }

    function options() {
        return array_merge(
            self::$defaults,
            $this->opts
        );
    }

    function set_message($m) {
        $this->message = $m;
    }
}
