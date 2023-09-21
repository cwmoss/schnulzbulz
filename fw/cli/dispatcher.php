<?php

namespace xorc\cli;

class dispatcher {

    public $sys;

    public function __construct(public parser $parser, public string $directory, public string $name) {
        $this->sys = __DIR__ . '/scripts';
    }

    public function dispatch() {
        $cmd = $this->parser->command;
        if (!$cmd) {
            $this->show_commands();
            return;
        }
        foreach ($this->get_scripts() as $s) {
            $name = basename($s, '.php');
            if ($name == $cmd) {
                if ($this->parser->get_switch('h', 'help')) {
                    print $this->show_help($s);
                    return false;
                }
                return $s;
            }
        }
        print $this->not_found($cmd);
        return false;
    }

    public function get_scripts() {
        $files = [];
        foreach ([$this->directory, $this->sys] as $dir) {
            $files = array_merge($files, glob("$dir/[!_.]*.php"));
        }
        return $files;
    }

    public function show_commands() {
        $scripts = $this->get_scripts();
        // var_dump($scripts);
        foreach ($scripts as $s) {
            $name = basename($s, '.php');
            printf("\t%s\n", $name);
        }
    }
    public function show_help($file_name) {
        $comments = array_filter(
            token_get_all(file_get_contents($file_name)),
            function ($entry) {
                return $entry[0] == \T_DOC_COMMENT;
            }
        );

        $help = array_shift($comments);
        if ($help) $help = $help[1];
        $help = str_replace(['/**', '*/'], '', $help);
        return $help;
    }

    public function not_found($cmd) {
        return "Command $cmd could not be found.\n";
    }
}
