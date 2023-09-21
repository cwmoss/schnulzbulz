<?php

namespace xorc\ar;

use xorc\ar\operator as OP;

class querybuilder {

    private $params;
    public $statement;
    public $fields = '*';
    public $order = "";
    public $limit = null;
    public $offset = null;
    public $page = null;
    public $conditions = [];
    public $set = [];
    public $values = [];

    public function __construct(public ?string $table = null, public $runner = null) {
    }

    public static function new($table = null, $query = null) {
        $q = (new static($table));
        if ($query) $q->where($query);
        return $q;
    }

    public static function new_from_array($table, $query) {
        return (new static($table))->where($query);
    }
    /*
        todo "REGEXP_LIKE(name, :name)"

        "name IS NOT NULL"
        name, OP::notnull
        name, null, OP::notnull
        [name => OP:notnull]
        
        name, HARRY, '='
        name, HARRY
        name, HARRY, OP::eq
        ['name' => HARRY]
        ['name' => [HARRY, '=']]
        
        ['name' => HARRY, 'dob IS NOT NULL']
        ['name' => HARRY, [dob => OP::notnull]]

    */
    public function table($t) {
        $this->table = $t;
        return $this;
    }
    public function _add_conditions($lop, ...$args) {
        if (is_array($args[0])) {
            foreach ($args[0] as $k => $v) {
                if (is_int($k)) {
                    // dbg_flush("val", $this, $v);
                    // die();
                    // constanter string
                    $this->_add_conditions($lop, $v);
                } else {
                    if (is_array($v)) {
                        $this->_add_conditions($lop, $k, ...$v);
                    } else {
                        $this->_add_conditions($lop, $k, $v);
                    }
                }
            }
            return $this;
        }
        $numargs = count($args);
        $this->conditions[] = match (true) {
            $numargs == 1 => [$lop, $args[0], null, OP::nop],
            $numargs == 2 && $args[1] instanceof OP => [$lop, $args[0], null, $args[1]],
            $numargs == 2 => [$lop, $args[0], $args[1], OP::eq],
            $numargs == 3 && !($args[2] instanceof OP) => [$lop, $args[0], $args[1], OP::from($args[2])],
            default => [$lop, ...$args]
        };
        return $this;
    }

    public function where(...$args) {
        return $this->_add_conditions(OP::AND, ...$args);
    }

    public function and(...$args) {
        return $this->_add_conditions(OP::AND, ...$args);
    }
    public function or(...$args) {
        return $this->_add_conditions(OP::OR, ...$args);
    }

    public function set(string | array $name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }
        $this->set[$name] = $value;
        return $this;
    }

    public function value(string | array $name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->value($k, $v);
            }
            return $this;
        }
        $this->values[$name] = $value;
        return $this;
    }

    public function select() {
        $this->statement = 'SELECT';
        return $this;
    }
    public function update(?array $values = null) {
        $this->statement = 'UPDATE';
        if ($values) $this->set($values);
        return $this;
    }
    public function insert() {
        $this->statement = 'INSERT';
        return $this;
    }
    public function delete() {
        $this->statement = 'DELETE';
        return $this;
    }

    // special select, useful to send a follow-up query for pagination
    public function count() {
        $this->statement = 'COUNT';
        return $this;
    }

    public function fields($f) {
        $this->fields = $f;
        return $this;
    }

    public function order($order) {
        $this->order = $order;
        return $this;
    }

    public function limit($limit, $page = null, $offset = null) {
        $this->limit = (int) $limit;
        if (!$offset) {
            // if (!$page) $page = 1;
            if ($page) $offset = ($page - 1) * $limit;
        }
        $this->offset = $offset;
        $this->page = $page;
        return $this;
    }

    public function _statement() {
        return match ($this->statement) {
            'UPDATE' => sprintf('UPDATE %s %s', $this->table, $this->_set()),
            'INSERT' => sprintf('INSERT INTO %s %s', $this->table, $this->_values()),
            'DELETE' => sprintf('DELETE FROM %s', $this->table),
            'SELECT', ($this->table ? null : '--') => sprintf('SELECT %s FROM %s', $this->fields, $this->table),
            'COUNT' => sprintf('SELECT COUNT(*) FROM %s', $this->table),
                // only (fragment) condition
            default => ''
        };
    }

    public function _set() {
        $sql = [];
        foreach ($this->set as $name => $value) {
            $sql[] = $name . ' = ?';
            $this->params[] = $value;
        }
        return 'SET ' . join(', ', $sql);
    }

    public function _values() {
        $placeholder = str_repeat('?, ', count($this->values) - 1) . '?';
        $this->params = [...array_keys($this->values), ...array_values($this->values)];
        return sprintf('(%s) VALUES (%s)', $placeholder, $placeholder);
    }

    public function _order() {
        if ($this->order) {
            return "ORDER BY " . $this->order;
        }
    }

    public function _limit() {
        if ($this->limit) {
            $sql = "LIMIT " . $this->limit;
            if ($this->offset) $sql .= " OFFSET " . $this->offset;
            return $sql;
        }
    }

    public function _conditions() {
        $c = [];
        foreach ($this->conditions as $idx => [$lop, $col, $val, $op]) {
            [$sql, $modified_params] = $op->sql($col, $val) + ['', null];
            // dbg("col-val-op", $col, $val, $op, $sql, $modified_params);
            $c[] = $idx ? $lop->value . ' ' . $sql : $sql;
            // default
            if ($modified_params === null) $this->_params_add($val);
            elseif ($modified_params !== OP::nop) $this->_params_add($modified_params);
        }
        return $c ? "WHERE " . join(' ', $c) : "";
    }

    public function _params_add($val) {
        if (is_array($val)) {
            $this->params = [...$this->params, ...$val];
        } else {
            $this->params[] = $val;
        }
    }
    public function build() {
        $this->params = [];
        if ($this->statement == 'SELECT' || ($this->statement == null && $this->table)) {
            $sql = array_filter([$this->_statement(), $this->_conditions(), $this->_order(), $this->_limit()]);
        } else {
            $sql = array_filter([$this->_statement(), $this->_conditions()]);
        }
        return [join(" ", $sql), $this->params];
    }
    public function run() {
        if (!$this->runner) return $this->build();
        return ($this->runner)(...)($this);
    }
    public function __toString() {
        return $this->build()[0];
    }
}
