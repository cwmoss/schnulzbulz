<?php

namespace ein\zeux;

class hu {
    public $data = ['normal' => 'N', 'n ormal' => 'M', '18' => 'Ö'];

    function __get($attr) {
        return $this->data[$attr];
    }

    function show() {
        foreach ($this->data as $k => $v) {
            print $k . ': ' . $this->$k . "\n"; // {"$k"}
        }
    }
}

$a = new hu;

print get_class($a);

var_dump(explode('\\zeux\\', get_class($a)));

print $a->{'n ormal'} . "\n";

$a->show();
