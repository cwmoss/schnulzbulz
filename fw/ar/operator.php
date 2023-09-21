<?php

namespace xorc\ar;

enum operator: string {

    case eq = '=';
    case neq = '!=';
    case gt = '>';
    case lt = '<';
    case gte = '>=';
    case lte = '<=';
    case regexp = '~';
    case regexp_like = 'REGEXP_LIKE';
    case between = 'BETWEEN';
    case like = 'LIKE';
    case ilike = 'ILIKE';
    case in = 'IN';
    case notin = 'NOT IN';
    case null = 'IS NULL';
    case notnull = 'IS NOT NULL';

    case fragment = 'FRAGMENT';

        // fixed expression without operator
    case nop = 'NOP';

        // logic
    case AND = 'AND';
    case OR = 'OR';

    function sql_named_parameter($name, $data) {
        return match ($this) {
            self::null, self::notnull => [sprintf('%s %s', $name, $this->value), self::nop],
            self::nop, self::fragment => [$name, $this],
            self::between => ['BETWEEN :' . $name],
            self::regexp_like => [sprintf('%s(%s, :%s)', $this->value, $name, $name)],
            default => [sprintf('%s %s :%s', $name, $this->value, $name)]
        };
    }

    function sql($name, $data) {
        return match ($this) {
            self::in, self::notin => [sprintf('%s %s (%s)', $name, $this->value, str_repeat('?, ', count($data) - 1) . '?')],
            self::null, self::notnull => [sprintf('%s %s', $name, $this->value), self::nop],
            self::nop => [$name, $this],
            self::fragment => [$name],
            self::between => [sprintf('%s BETWEEN ? AND ?', $name)],
            self::regexp_like => [sprintf('%s(%s, ?)', $this->value, $name)],
            default => [sprintf('%s %s ?', $name, $this->value)]
        };
    }
}

/*

=
like
regexp_like
BETWEEN
in
~ db_regex_funktion()


*/