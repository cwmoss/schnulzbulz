<?php

namespace xorc\ar;

use Exception;
use Generator;
use JsonSerializable;
use xorc\ar\exception\not_found;
use xorc\db\pdox;

abstract class base implements JsonSerializable {

    private $record = null;
    // changed attributes
    private $dirty = [];
    // mass assignment protected attributes
    private $protected = [];
    // more columns in query result, than attributes in table
    private $bucket = [];
    private $loaded_from_store = false;
    private $saved_to_store = false;

    public static function new($attributes) {
        $o = new static();
        $o->set($attributes);
        return $o;
    }

    static public function from_db(array $row) {
        $o = new static();
        $o->set_record($row);
        return $o;
    }

    public function set(array $attributes, $protected = true) {
        foreach ($attributes as $name => $value) {
            if (
                !$protected ||
                (!static::is_pk($name) && !isset($this->protected[$name]))
            ) {
                $this->$name = $value;
            }
        }
    }

    public function __get($attr) {
        if (is_null($this->record)) $this->init_record();
        if (method_exists($this, 'get_' . $attr)) return [$this, 'get_' . $attr](...)();
        if (reflection::column_exists($this::class, $attr)) {
            return $this->record[$attr] ?? null;
        }
        // if (!array_key_exists($attr, $this->record)) throw new Exception('cant read attr ' . $attr);
        throw new Exception('cant read attr ' . $attr);
        return $this->record[$attr];
    }

    public function __set($attr, $val) {
        if (is_null($this->record)) $this->init_record();
        if (method_exists($this, 'set_' . $attr)) return [$this, 'set_' . $attr](...)($val);
        if (reflection::column_exists($this::class, $attr)) {
            // if (!in_array($attr, $this->dirty)) $this->dirty[] = $attr;
            $this->dirty[$attr] = $this->record[$attr] ?? null;
            return $this->record[$attr] = $val;
        }
        // if (!array_key_exists($attr, $this->record)) throw new Exception('cant write attr ' . $attr);
        throw new Exception('cant write attr ' . $attr);
        $this->record[$attr] = $val;
    }

    public function auto_columns() {
    }

    public function save() {
        return $this->insert_or_update();
    }

    // fresh data fetch
    public function refresh() {
        $q = querybuilder::new_from_array(static::table(), static::pk($this->id))->select()->limit(1)->build();
        // dbg($q, $q->build());
        $res = static::connection()->select_first_row(...$q);
        $this->set_record($res);
    }

    private function insert_or_update($validate = true) {
        if (($ok = $this->before_validation()) === false) {
            return $ok;
        }
        if (!$this->loaded_from_store && !$this->saved_to_store) {
            if (
                $this->before_validation_on_create() !== false &&
                $this->after_validation() !== false &&
                $this->after_validation_on_create() !== false &&
                $this->before_save() !== false &&
                $this->before_create() !== false
            ) {
                $this->auto_columns();

                $ok = $this->insert();

                if ($this->after_create() === false || $this->after_save() === false) {
                    return $ok;
                }
            }
        } else {
            if (
                $this->before_validation_on_update() !== false &&
                $this->after_validation() !== false &&
                $this->after_validation_on_update() !== false &&
                $this->before_save() !== false &&
                $this->before_update() !== false
            ) {
                $this->auto_columns();

                $ok = $this->update();

                if ($this->after_update() === false || $this->after_save() === false) {
                    return $ok;
                }
            }
        }
        return $ok;
    }

    private function insert() {
        // empty?
        if (is_null($this->record)) $this->init_record();
        // dd($this->record);
        $res = static::connection()->insert(static::table(), $this->record);
        // TODO: pk with different name, multiple pk
        $idreturned_after_insert = true;
        if ($idreturned_after_insert) {
            $this->id = $res;
        }
        // default values?
        if (array_diff(reflection::column_names($this), array_keys($this->record))) {
            $this->refresh();
        }
    }

    private function update() {
        $q = new querybuilder(static::table());
        $values = array_intersect_key($this->record, $this->dirty);
        if (!$values) return true;
        $upd = $q->update()->set($values)->where(static::pk($this->id))->build();
        // TODO: affected rows check if it equals 1
        $affected = static::connection()->queryx(...$upd, affected: true);
        $this->saved_to_store = true;
        $this->dirty = [];
    }

    public function destroy(): bool {
        if ($this->before_destroy() !== false) {
            $del = querybuilder::new(static::table(), static::pk($this->id))->delete()->build();
            $affected = static::connection()->queryx(...$del, affected: true);
            if ($affected == 1) {
                return $this->after_destroy();
            } else {
                return false;
            }
        }
        return false;
    }

    private function init_record() {
        // TODO: nulls!
        // $this->record = array_fill_keys(reflection::column_names($this::class), null);
        $this->record = [];
        // var_dump($this);
    }

    private function set_record($r) {
        $this->record = $r;
        $this->loaded_from_store = true;
        $this->dirty = [];
    }


    public function dirty() {
        return array_keys($this->dirty);
    }

    public function attribute_present($attr) {
        return isset($this->record[$attr]);
    }

    public function has_attribute($attr) {
        return reflection::column_exists($this::class, $attr);
    }

    public function be_new() {
        $this->loaded_from_store = $this->saved_to_store = false;
    }
    public function was_loaded() {
        $this->loaded_from_store = true;
    }

    public function is_new_record() {
        return (!$this->loaded_from_store && !$this->saved_to_store);
    }

    public function is_created_record() {
        return (!$this->loaded_from_store && $this->saved_to_store);
    }

    public static function pk_query($id): array {
        $q = 'SELECT * FROM %s WHERE id = :id LIMIT 1';
        $q = sprintf($q, static::table());
        return [$q, ['id' => $id]];
    }

    public static function pk($id): array {
        if (!is_array($id)) {
            return ['id' => $id];
        }
        return $id;
    }
    public static function is_pk($name) {
        return $name == 'id';
    }

    public static function find($id) {
        $q = querybuilder::new_from_array(static::table(), static::pk($id))->select()->limit(1)->build();
        // dbg($q, $q->build());

        $res = static::connection()->select_first_row(...$q);
        if (is_null($res)) throw new not_found('Primary key not found in table ' . static::table());
        return static::from_db($res);
    }

    public static function find_all(array | querybuilder | null $query = null) {
        $query = match (gettype($query)) {
            'array' => querybuilder::new(static::table(), $query),
            'NULL' => querybuilder::new(static::table()),
                // table could be blank
            default => $query->table(static::table())
        };
        $query->select();
        dbg("+++ find_all query", $query);
        return static::run_query(clone $query);
    }

    public static function find_first(array | querybuilder | null $query = null) {
        $query = match (gettype($query)) {
            'array' => querybuilder::new(static::table(), $query),
            'NULL' => querybuilder::new(static::table()),
            default => $query
        };
        $res = static::connection()->select_first_row(...($query->select()->limit(1)->build()));
        if (is_null($res)) return $res;
        return static::from_db($res);
    }

    public static function get_pager(querybuilder $query) {
        $q = $query->count()->build();
        // dd($q, $query);
        $total = static::connection()->select_first_cell(...$q);
        return new pager($total, $query->limit, $query->page);
    }
    public static function destroy_all(array | querybuilder | null $query = null) {
        $aff = 0;
        foreach (static::find_all($query) as $o) {
            $res = $o->destroy();
            $aff += $res ? 1 : 0;
        }
        return $aff;
    }

    public static function delete_all(array | querybuilder | null $query = null) {
        $query = match (gettype($query)) {
            'array' => querybuilder::new(static::table(), $query),
            'NULL' => querybuilder::new(static::table()),
            default => $query
        };
        $del = $query->delete()->build();
        $affected = static::connection()->queryx(...$del, affected: true);
        return $affected;
    }

    public static function update_all(array | querybuilder $set = null, $where = null) {
        $query = match (gettype($set)) {
            'array' => querybuilder::new(static::table())->set($set),
            default => $set
        };
        if ($where) $query->where($where);
        $upd = $query->update()->build();
        $affected = static::connection()->queryx(...$upd, affected: true);
        return $affected;
    }

    public static function new_query_builder() {
        return (new querybuilder(static::table()));
    }

    public static function run_query($builder): Generator {
        dbg("++ run query ++");
        [$q, $params] = $builder->build();
        #var_dump($params);
        #exit;
        $res = static::connection()->select($q, $params);
        foreach ($res as $rec) {
            yield static::from_db($rec);
        }
        return $res;
    }
    public static function attribute_names() {
        $a = reflection::column_names(static::class);
        sort($a);
        return $a;
    }

    public static function count(): int {
        $q = 'SELECT count(*) FROM %s';
        $q = sprintf($q, static::table());
        return static::connection()->select_first_cell($q);
    }

    public static function table() {
        return reflection::table(static::class);
    }

    public static function define_schema() {
        return ['table' => naming::plural(naming::class_basename(static::class))];
    }

    // actually directly the database!
    public static function connection(): pdox {
        return connector::get(reflection::db(static::class))->db;
    }

    // jsonserializable
    public function jsonSerialize(): mixed {
        return $this->record;
    }

    public function __toString() {
        return json_encode($this, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }

    /*
        lifecycle
    */
    public function after_load() {
        return true;
    }
    public function after_create() {
        return true;
    }
    public function after_destroy() {
        return true;
    }
    public function after_save() {
        return true;
    }
    public function after_update() {
        return true;
    }
    public function after_validation() {
        return true;
    }
    public function after_validation_on_create() {
        return true;
    }
    public function after_validation_on_update() {
        return true;
    }
    public function before_create() {
        return true;
    }
    public function before_destroy() {
        return true;
    }
    public function before_save() {
        return true;
    }
    public function before_update() {
        return true;
    }
    public function before_validation() {
        return true;
    }
    public function before_validation_on_create() {
        return true;
    }
    public function before_validation_on_update() {
        return true;
    }

    public function after_find() {
    }
    public function after_initialize() {
    }
}
