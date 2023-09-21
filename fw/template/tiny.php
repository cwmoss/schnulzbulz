<?php

namespace xorc\template;


class tiny {

    public static $cache = array();
    public static $options = array();
    public $c = null;

    //** instance for single template mode
    public function __construct($str = null) {
        $this->c = $str;
    }

    // ad-hoc, ohne cache rendern
    public function __invoke($str, $data = [], $lft = '{', $rgt = '}') {
        $tpl = self::compile($str, 0, preg_quote($lft, '!'), preg_quote($rgt, '!'));
        # print(json_encode($tpl, JSON_PRETTY_PRINT));
        #print_r($tpl);
        return self::resolve($tpl, $data);
    }
    public function set_template($str) {
        $this->c = $str;
    }

    public function display($data) {
        if (is_null($this->c)) return false;
        if (!is_array($this->c)) $this->c = self::compile($this->c);
        return self::resolve($this->c, $data);
    }

    static function create($str) {
        return new self($str);
    }

    //** globale optionen zzt. lft/rgt tag
    static function set_options($opts) {
        self::$options = $opts;
    }

    //** cache (or maybe snippet) functions for named templates
    static function cache($name, $set = null) {
        #print "NAME: $name\n";
        if (!is_null($set)) self::$cache[$name] = $set;
        return self::$cache[$name];
    }

    static function render_snippet($name, $data = array(), $snip = null) {
        $tpl = self::cache($name);
        if (!is_array($tpl)) $tpl = self::cache($name, self::compile($tpl));
        return self::resolve($tpl, $data);
    }

    // multi-template strings (eg. from files)
    static function add_snippets($str, $opts = array()) {
        $opts = array_merge(array('delim' => '@@@', 'lazy' => true), (array) $opts);
        $delim = $opts['delim'];
        $mat = preg_split("!^\s*$delim\s*([-\w]+)\s*$delim\s*$!ms", $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; $i < count($mat);) {
            self::cache($mat[$i++], $mat[$i++]);
        }
    }


    static function snippet_exists($name) {
        return isset(self::$cache[$name]);
    }

    //** functions



    static function resolve($tpl, $data = array(), $level = 0) {
        $print = array($tpl[0]);
        foreach ($tpl[1] as $k => $name) {
            // blöcke
            if (is_array($name)) {
                $bname = $name[0][0];
                $n = $name[0][1];
                if ($bname == 'if' || $bname == 'not') {
                    if (($bname == 'if' && @$data[$n]) || ($bname == 'not' && !@$data[$n])) $print[] = self::resolve($name[1], $data, $level + 1);
                    else $print[] = "";
                } elseif ($bname == 'each') {
                    $out = "";
                    $count = 0;
                    if ($data[$n]) foreach ($data[$n] as $key => $row) {
                        if (!$count) $row['@first'] = true;
                        $row['@num'] = ++$count;
                        if ($count == count($data[$n])) $row['@last'] = true;
                        $row['@key'] = $key;
                        $out .= self::resolve($name[1], $row, $level + 1);
                    }
                    $print[] = $out;
                }
                // variablen
            } else {
                $print[] = @$data[$name];
            }
        }

        return call_user_func_array('sprintf', $print);
    }

    static function compile($tpl, $level = 0, $lft = '{', $rgt = '}') {
        # print "compiling\n";
        # if(!$level) print "compiling\n";
        $compiled = [];
        $count = 0;
        $tpl = preg_replace_callback("!$lft(if|not|each)\s+([@\w]+)$rgt(.*?)$lft/\\1\s+\\2$rgt!sm", function ($mat) use (&$count, &$compiled, &$blocks) {
            $count++;
            $compiled[] = [[$mat[1], $mat[2]], $mat[3]];
            return '%' . $count . '$s';
        }, $tpl ?: "");

        // +(\s+".*?")*?)*?
        $tpl = preg_replace_callback("!($lft([@\w]+)(|\w+)?$rgt)!", function ($mat) use (&$count, &$compiled) {
            $count++;
            $compiled[] = $mat[2];
            if ($mat[3]) {
                #            print "FILTER:\n";
                #            print_r($mat);
            }
            return '%' . $count . '$s';
        }, $tpl);

        foreach ($compiled as &$c) {
            if (is_array($c)) $c[1] = self::compile($c[1], $level + 1, $lft, $rgt);
        }
        return [$tpl, $compiled];
    }
}

/* #140
   rw, 20.3.2015
   
   templates für mini partials, mini layouts
      z.b. formular zeilen
      
      beispiel
      ========
      <div><div>{label}</div>
         {if error}<div>error: {error}</div>{/if error}
         {not error}<p>alles super</p>{/not error}
         <div class="inhalt">{form}</div>
      </div>
      {each lines}
         <p>zeile {@num}: name: {name}, alter: {age} {if @last} **ende** {/if @last}</p>
      {/each lines}
      {not lines}<i>keine personen gefunden</i>{/not lines}


      compiliert
      ==========
      Array
      (
          [0] => <div><div>%5$s</div>
         %1$s
         %2$s
         <div class="inhalt">%6$s</div>
      </div>
      %3$s
      %4$s

          [1] => Array
              (
                  [0] => Array
                      (
                          [0] => Array
                              (
                                  [0] => if
                                  [1] => error
                              )

                          [1] => Array
                              (
                                  [0] => <div>error: %1$s</div>
                                  [1] => Array
                                      (
                                          [0] => error
                                      )

                              )

                      )

                  [1] => Array
                      (
                          [0] => Array
                              (
                                  [0] => not
                                  [1] => error
                              )

                          [1] => Array
                              (
                                  [0] => <p>alles super</p>
                                  [1] => Array
                                      (
                                      )

                              )

                      )

                  [2] => Array
                      (
                          [0] => Array
                              (
                                  [0] => each
                                  [1] => lines
                              )

                          [1] => Array
                              (
                                  [0] => 
         <p>zeile %2$s: name: %3$s, alter: %4$s %1$s</p>

                                  [1] => Array
                                      (
                                          [0] => Array
                                              (
                                                  [0] => Array
                                                      (
                                                          [0] => if
                                                          [1] => @last
                                                      )

                                                  [1] => Array
                                                      (
                                                          [0] =>  **ende** 
                                                          [1] => Array
                                                              (
                                                              )

                                                      )

                                              )

                                          [1] => @num
                                          [2] => name
                                          [3] => age
                                      )

                              )

                      )

                  [3] => Array
                      (
                          [0] => Array
                              (
                                  [0] => not
                                  [1] => lines
                              )

                          [1] => Array
                              (
                                  [0] => <i>keine personen gefunden</i>
                                  [1] => Array
                                      (
                                      )

                              )

                      )

                  [4] => label
                  [5] => form
              )

      )
*/