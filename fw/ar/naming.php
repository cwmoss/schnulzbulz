<?php

namespace xorc\ar;

class naming {

    public static $uncountable_words = [
        "equipment", "information", "rice", "money",
        "species", "series", "fish"
    ];

    public static $plural_rules = [
        array('/^(ox)$/i', '\1\2en'),                    // ox
        array('/([m|l])ouse$/i', '\1ice'),            // mouse, louse
        array('/(matr|vert)ix|ex$/i', '\1ices'),      // matrix, vertex, index
        array('/(x|ch|ss|sh)$/i', '\1es'),            // search, switch, fix, box, process, address
        array('/([^aeiouy]|qu)ies$/i', '\1y'),
        array('/([^aeiouy]|qu)y$/i', '\1ies'),        // query, ability, agency
        array('/(hive)$/i', '\1s'),                   // archive, hive
        array('/(?:([^f])fe|([lr])f)$/i', '\1\2ves'), // half, safe, wife
        array('/sis$/i', 'ses'),                      // basis, diagnosis
        array('/([ti])um$/i', '\1a'),                 // datum, medium
        array('/(p)erson$/i', '\1eople'),             // person, salesperson
        array('/(m)an$/i', '\1en'),                   // man, woman, spokesman
        array('/(c)hild$/i', '\1hildren'),            // child
        array('/(buffal|tomat)o$/i', '\1\2oes'),                  // buffalo, tomato
        array('/(bu)s$/i', '\1\2ses'),                // bus
        array('/(alias)/i', '\1es'),                  // alias
        array('/(octop|vir)us$/i', '\1i'),            // octopus, virus - virus has no defined plural 
        // (according to Latin/dictionary.com), but viri is better than viruses/viruss
        array('/(ax|cri|test)is$/i', '\1es'),         // axis, crisis
        array('/s$/i', 's'),                          // no change (compatibility)
        array('/$/', 's'),
    ];

    public static $singular_rules = array(
        array('/(matr)ices$/i', '\1ix'),
        array('/(vert)ices$/i', '\1ex'),
        array('/^(ox)en/i', '\1'),
        array('/(alias)es$/i', '\1'),
        array('/([octop|vir])i$/i', '\1us'),
        array('/(cris|ax|test)es$/i', '\1is'),
        array('/(shoe)s$/i', '\1'),
        array('/(o)es$/i', '\1'),
        array('/(bus)es$/i', '\1'),
        array('/([m|l])ice$/i', '\1ouse'),
        array('/(x|ch|ss|sh)es$/i', '\1'),
        array('/(m)ovies$/i', '\1\2ovie'),
        array('/(s)eries$/i', '\1\2eries'),
        array('/([^aeiouy]|qu)ies$/i', '\1y'),
        array('/([lr])ves$/i', '\1f'),
        array('/(tive)s$/i', '\1'),
        array('/(hive)s$/i', '\1'),
        array('/([^f])ves$/i', '\1fe'),
        array('/(^analy)ses$/i', '\1sis'),
        array('/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i', '\1\2sis'),
        array('/([ti])a$/i', '\1um'),
        array('/(p)eople$/i', '\1\2erson'),
        array('/(m)en$/i', '\1an'),
        array('/(s)tatus$/i', '\1\2tatus'),
        array('/(c)hildren$/i', '\1\2hild'),
        array('/(n)ews$/i', '\1\2ews'),
        array('/s$/i', ''),
    );


    public static function singular($t) {
        $t = strtolower(trim($t));
        if (array_search($t, self::$uncountable_words)) {
            return $t;
        }
        foreach (self::$singular_rules as $r) {
            if (preg_match($r[0], $t)) {
                return preg_replace($r[0], $r[1], $t);
            }
        }
        return $t;
    }

    public static function plural($t) {
        $t = strtolower(trim($t));
        if (array_search($t, self::$uncountable_words)) {
            return $t;
        }
        foreach (self::$plural_rules as $r) {
            if (preg_match($r[0], $t)) return preg_replace($r[0], $r[1], $t);
        }
        return $t;
    }

    static function class_basename($object_or_string) {
        if (is_object($object_or_string)) $object_or_string = $object_or_string::class;
        $name = strtolower($object_or_string);
        $name = substr(strrchr($name, '\\'), 1);
        return $name;
    }
}
