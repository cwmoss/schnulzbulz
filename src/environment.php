<?php

namespace oda;

use twentyseconds;
use xorc\tiny_template;
use oda\model\hook;
use oda\model\validations_provider;
use xorc\template\tiny;

class environment {

    public string $client;
    public string $lang;
    public string $lang_short;
    public string $theme;

    public function __construct(public \xorc\config $config) {
        $this->client = $_SERVER['ODA_CLIENT'] ?? 'default';
        $this->lang = $_SERVER['ODA_LANG'] ?? 'de_DE.UTF-8';

        $this->lang_short = substr($this->lang, 0, 2);
        $this->theme = ($this->client == 'default') ? '' : ($_SERVER['ODA_THEME'] ?? $_SERVER['ODA_CLIENT']);
        $this->setup();
        $this->setup_theme($this->theme);
        $this->setup_validations();
    }

    public function setup() {
        $domain = 'bob-' . $this->client;
        $this->setup_language($domain);
    }

    function setup_language($domain = "") {
        $ok = setlocale(LC_MESSAGES, $this->lang);
        #   var_dump($ok);

        $ok = bindtextdomain($domain, $this->config->base . "/locale");
        bind_textdomain_codeset($domain, 'UTF-8');
        #   var_dump($ok);

        $ok = textdomain($domain);
        $ok = setlocale(LC_TIME, $this->lang);
        #   var_dump($ok); 
        #   var_dump(_('message')); 
    }

    function setup_validations() {
        // log_error("+++++ client-conf", ODA_CLIENT_CONF);
    }
    function setup_theme($theme) {
        $this->load_functions($theme);
    }

    function xxsetup_theme($ctrl = null) {
        // log_error("#### SETUP THEME #####");
        if (!$_SERVER['ODA_CLIENT']) $_SERVER['ODA_CLIENT'] = 'default';

        if ($_SERVER['ODA_CLIENT'] != 'default') {
            $theme = $_SERVER['ODA_THEME'] ?: $_SERVER['ODA_CLIENT'];
            if (!$ctrl) $ctrl = Xorcapp::$inst->ctrl;
            if ($ctrl) {
                $ctrl->theme($theme);
            } else {
                // log_error("+++++ no ctrl");
            }

            XorcRuntimeException::$defaults = array_merge(
                XorcRuntimeException::$defaults,
                array(
                    "theme" => $theme,
                )
            );
        }

        define('ODA_CLIENT_CONF', Xorcapp::$inst->approot . '/config/' . $_SERVER['ODA_CLIENT'] . '/');
        #   $this->S = BOB_Service::i();

        # hier auch schon die sprache setzen
        $client_lang = "bob-" . $_SERVER['ODA_CLIENT'];
        setup_language($client_lang);

        twentyseconds\validation\validator::$base = XORC_APP_BASE;
        twentyseconds\validation\validator::$opts['dir'] = ODA_CLIENT_CONF;
        twentyseconds\validation\validator::get_providers();
        twentyseconds\validation\validator::add_provider(new oda_validations_provider);

        #   Validator::load(BOB_CLIENT_CONF."/validations.db");
        $this->load_functions($theme);

        // zur sicherheit, falls es innerhalb einer exception aufgerufen wird
        // und der bootvorgang noch nicht abgeschlossen wurde
        if (Xorcapp::$inst->ctrl) {
            twentyseconds\validation\validator::set_message_rewrites(hook::invoke('validation_message_vars', []));
        }
    }

    function load_functions($theme = "") {

        if ($theme) {
            $custdir = $this->config->base . '/themes/' . $theme;
            $tpl = $custdir . '/view/__tiny.tpl.html';
            if (file_exists($tpl)) {
                tiny::add_snippets(file_get_contents($tpl));
            }

            include_once($custdir . '/functions.php');
            hook::load($custdir . '/hooks.php');
        }
        include("functions.php");
    }
}
