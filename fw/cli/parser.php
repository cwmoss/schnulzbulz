<?php

namespace xorc\cli;

class parser {

    // $ miggi init
    public string $command = "";

    // $ miggi migrate --yes
    public array $switches = [];

    // $ miggi --dir=db/migrations
    public array $opts = [];

    // $ miggi new create_todos_tables
    public array $args = [];

    // bin/miggi.php
    public string $script = "";

    public function __construct(array $args) {
        $this->script = array_shift($args);
        $this->parse($args);
    }

    public function parse(array $args) {

        foreach ($args as $token) {
            if (preg_match('/^--([^=]+)=(.*)/', $token, $match)) {
                $this->opts[$match[1]] = $match[2];
            } elseif (preg_match('/^--([^=]+)/', $token, $match)) {
                $this->switches[$match[1]] = true;
            } elseif (preg_match('/^-([^=])=(.*)/', $token, $match)) {
                $this->opts[$match[1]] = $match[2];
            } elseif (preg_match('/^-([^=])/', $token, $match)) {
                $this->switches[$match[1]] = true;
            } else {
                $this->args[] = $token;
            }
        }
        if ($this->args) {
            $this->command = array_shift($this->args);
        }
    }

    function get_opt(...$tests) {
        // print_r($tests);
        foreach ($tests as $t) {
            if (isset($this->opts[$t])) return $this->opts[$t];
        }
        return null;
    }

    function get_switch(...$tests) {
        // print_r($tests);
        foreach ($tests as $t) {
            if (isset($this->switches[$t])) return true;
        }
        return false;
    }
}
