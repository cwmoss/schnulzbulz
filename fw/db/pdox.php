<?php

namespace xorc\db;

/*
https://phpdelusions.net/pdo/sql_injection_example
*/

use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\AbstractLogger;

class pdox extends PDO {
    protected $_C;

    protected $_logTarget = null;
    protected $_slowLogOffset = null;
    protected $_inTransaction = false;

    protected $_nameOpening;
    protected $_nameClosing;

    protected $_tableGatewayCache;
    protected $_tableNameMapping = array();
    protected $_informationSchema;
    protected $_cacheEnabled = false;
    protected $_allowNestedTransaction = false;
    protected $_transactionLevel = 0;


    public function __construct($dsn, $user = null, $password = null, public ?AbstractLogger $logger = null, array $options = []) {

        $t = microtime(true);
        parent::__construct($dsn, $user, $password, $options);
        $this->log('CONNECT', $t);

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [pdox_statement::class, [$this]]);

        switch ($this->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $this->_nameOpening = $this->_nameClosing = '`';
                break;

            case 'mssql':
                $this->_nameOpening = '[';
                $this->_nameClosing = ']';
                break;

            case 'sqlite':
                /* $this->sqliteCreateAggregate(
                    "group_concat",
                    array($this, '__sqlite_group_concat_step'),
                    array($this, '__sqlite_group_concat_finalize'),
                    2
                );*/
                // $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('pdoext_SQLiteStatement'));
                // fallthru

            default:
                $this->_nameOpening = $this->_nameClosing = '"';
                break;
        }
        // $this->_informationSchema = new pdoext_InformationSchema($this);
    }


    /**
     * Escapes names (tables, columns etc.)
     */
    public function quoteName($name) {
        $names = array();
        foreach (explode(".", $name) as $name) {
            $names[] = $this->_nameOpening
                . str_replace($this->_nameClosing, $this->_nameClosing . $this->_nameClosing, $name)
                . $this->_nameClosing;
        }
        return implode(".", $names);
    }

    public function insert($table, $vars) {
        $col_names = array_keys($vars);
        $placeholder_names = $this->placeholder_names($col_names);
        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            join(', ', $col_names),
            join(', ', $placeholder_names)
        );
        $this->queryx($query, $vars);
        return $this->lastInsertId();
    }

    // TODO: prefix condition vars
    //      ex: SET name = :name WHERE name = :prefix_name
    public function update($table, $setvars = [], $condition = '', $condition_vars = []) {
        $col_names = array_keys($setvars);
        $placeholder_names = $this->placeholder_names_set($col_names);
        $query = sprintf(
            'UPDATE %s SET %s %s',
            $table,
            join(', ', $placeholder_names),
            $condition
        );
        return $this->queryx($query, $setvars + $condition_vars);
    }

    public function select($q, $vars = []) {
        $res = $this->queryx($q, $vars);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function select_first_row($q, $vars = []): array|null {

        $res = $this->queryx($q, $vars);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        }
        return null;
    }

    public function select_first_cell($q, $vars = []): mixed {
        $res = $this->queryx($q, $vars);
        return $res->fetchColumn();
    }

    public function select_all_map($q, $vars = [], $multi = false): array {
        $res = $this->queryx($q, $vars);
        if (!$multi) {
            return $res->fetchAll(PDO::FETCH_KEY_PAIR);
        } else {
            return $res->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
        }
    }

    public function select_all_unique_cell($q, $vars = []) {
        $res = $this->queryx($q, $vars);
        return $res->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function queryx($query, $vars = [], $affected = false) {
        if (!$vars) {
            $this->query($query);
        }
        $t = microtime(true);
        $vars = $this->typed_vars($vars);
        try {
            $sth = $this->prepare($query);
            $res = $sth->execute($vars);
            if ($affected) {
                return (int) $sth->rowCount();
            }
        } finally {
            $this->log('QUERYX', $t, ['query' => $query, 'vars' => $vars]);
        }
        return $sth;
    }

    public function query(string $query, ?int $fetchMode = null, ...$fetchModeArgs): PDOStatement|false {
        $t = microtime(true);
        try {
            $res = parent::query($query, $fetchMode, ...$fetchModeArgs);
        } finally {
            $this->log('QUERY', $t, $query);
        }
        return $res;
    }

    public function log($op, $start_time, ...$data) {
        if ($this->logger) {
            if ($start_time) {
                $elapsed = microtime(true) - $start_time;
                // print $elapsed;

                if ($elapsed > 1) {
                    $print =  $elapsed . ' s';
                } elseif ($ms = ((int) ($elapsed * 1000))) {
                    $print =  $ms . ' ms';
                } else {
                    $print =  (int) ($elapsed * 1000000) . ' µs';
                }
                $op .= " ($print)";
            }

            $this->logger->log('debug', $op, $data);
        }
    }
    public function placeholder_names($cols) {
        return array_map(function ($col) {
            return ':' . $col;
        }, $cols);
    }
    public function placeholder_names_set($cols) {
        return array_map(function ($col) {
            return $col . ' = :' . $col;
        }, $cols);
    }
    public function typed_vars($cols) {
        return array_map(function ($col) {
            // if (ctype_digit((string) $col)) return (int) $col;
            if (is_null($col)) return 'NULL';
            if (is_bool($col)) return $col ? 1 : 0;
            return $col;
        }, $cols);
    }

    public function typed_vars_int($cols) {
        return array_map(function ($col) {
            if (is_numeric($col)) return (int) $col;
            return $col;
        }, $cols);
    }

    public function exec($query): int|false {
        $t = microtime(true);
        $res = parent::exec($query);
        $this->log('EXEC', $t, $query);
        return $res;
    }

    public function exec_sql_file($file) {
        $ddl = file_get_contents($file);
        $statements = explode('----', $ddl);
        #print $ddl;
        foreach ($statements as $ddl_s) {
            $ddl_s = trim($ddl_s);
            if (!$ddl_s || $ddl_s[0] == '#') continue;

            $this->exec($ddl_s);
        }
        return;
    }
}
