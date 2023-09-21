<?php

namespace xorc;

class config {

    public array $files;
    public array $conf;

    function __construct(public string $base, public string $appname, string $configfile = null) {
        $this->load_ini($configfile);
    }

    // APPNAME_CONF=oda_local.ini php -S localhost:9999 -t public/
    function load_ini($conf = null) {
        // via cli?
        if ($conf) {
            if ($conf[0] != '/' && ($conf[0] == '.' || str_starts_with($conf, 'config/'))) {
                $conf = realpath($conf);
            }
        } else {
            $envvar = strtoupper($this->appname) . '_CONF';
            $conf = $_SERVER[$envvar] ?? getenv($envvar);
        }
        if (!$conf && isset($_SERVER["XORC_ENV"])) {
            $envname = ['development' => 'dev', 'local' => 'local', 'docker' => 'docker', 'test' => 'test', 'production' => 'prod'][$_SERVER["XORC_ENV"]] ?? 'prod';
            $conf = $this->appname . '_' . $envname . '.ini';
        }
        if (!$conf) $conf =  $this->appname . '_prod.ini';

        if ($conf[0] != '/') {
            $conf = $this->base . "/config/" . $conf;
        }
        if (!is_readable($conf)) {
            throw new \Exception('Could not read ini file.');
        }
        $res = parse_ini_file($conf, true, \INI_SCANNER_TYPED);
        if ($res === false) {
            throw new \Exception('Could not parse ini file.');
        }
        $this->files[] = $conf;
        $this->conf = $res;
    }

    function setup() {
        if ($this->conf['general']['error_reporting'] ?? null) {
            // $err_rep = 0;
            // @eval('$err_rep = ' . $conf['general']['error_reporting'] . ';');
            // dd($this->conf['general']['error_reporting']);
            error_reporting($this->conf['general']['error_reporting']);
        }
    }
}
