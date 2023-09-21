<?php

namespace xorc\db;

use PDO;
use Exception;

/**
 * Provides access to introspection of the database schema.
 */
class schema {
    protected $connection;
    protected $has_many = array();
    protected $belongs_to = array();
    function __construct($connection) {
        $this->connection = $connection;
    }
    /**
     * Returns list of tables in database.
     */
    public function getTables() {
        switch ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $sql = "SHOW TABLES";
                break;
            case 'pgsql':
                $sql = "SELECT CONCAT(table_schema,'.',table_name) AS name FROM information_schema.tables
            WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog','information_schema')";
                break;
            case 'sqlite':
                $sql = 'SELECT name FROM sqlite_master WHERE type = "table"';
                break;
            default:
                throw new Exception();
        }
        $result = $this->connection->query($sql);
        $result->setFetchMode(PDO::FETCH_NUM);
        $meta = array();
        foreach ($result as $row) {
            $meta[] = $row[0];
        }
        return $meta;
    }
    /**
     * Returns reflection information about a table.
     */
    public function getColumns($table) {
        switch ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
                /** method for finding primary key see http://wiki.postgresql.org/wiki/Retrieve_primary_key_columns */
                $result = $this->connection->queryx(
                    "SELECT attname as column_name,
                    format_type(pg_attribute.atttypid, pg_attribute.atttypmod) as data_type,
                    pg_index.indisprimary,
                    pg_attrdef.adsrc as column_default
               FROM pg_attribute
          LEFT JOIN pg_index
                 ON pg_index.indrelid = pg_attribute.attrelid
                AND pg_attribute.attnum = any (pg_index.indkey)
                AND pg_index.indisprimary = true
          LEFT JOIN pg_attrdef
                 ON pg_attrdef.adrelid = pg_attribute.attrelid
                AND pg_attrdef.adnum = pg_attribute.attnum
              WHERE attrelid = :tableName::regclass
                AND attnum > 0
                AND attisdropped = false",
                    array(":tableName" => $table)
                );
                $result->setFetchMode(PDO::FETCH_ASSOC);
                $meta = array();
                foreach ($result as $row) {
                    $meta[$row['column_name']] = array(
                        'pk' => $row['indisprimary'] == 't',
                        'dbtype' => $row['data_type'],
                        'type' => columns::from_db($row['data_type']),
                        'blob' => preg_match('/(text|bytea)/', $row['data_type']),
                        'default' => $row['column_default']
                    );
                }
                return $meta;
            case 'sqlite':
                $result = $this->connection->query("PRAGMA table_info(" . $this->connection->quoteName($table) . ")");
                $result->setFetchMode(PDO::FETCH_ASSOC);
                $meta = array();
                foreach ($result as $row) {
                    $meta[$row['name']] = array(
                        'pk' => $row['pk'] == '1',
                        'dbtype' => $row['type'],
                        'type' => columns::from_db($row['type']),
                        'default' => null,
                        'blob' => preg_match('/(TEXT|BLOB)/', $row['type']),
                    );
                }
                return $meta;
            default:
                $result = $this->connection->queryx(
                    "select COLUMN_NAME, COLUMN_DEFAULT, DATA_TYPE, COLUMN_KEY from INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = DATABASE() and TABLE_NAME = :table_name",
                    array(':table_name' => $table)
                );
                $result->setFetchMode(PDO::FETCH_ASSOC);
                $meta = array();
                foreach ($result as $row) {
                    $meta[$row['COLUMN_NAME']] = array(
                        'pk' => $row['COLUMN_KEY'] == 'PRI',
                        'dbtype' => $row['DATA_TYPE'],
                        'type' => columns::from_db($row['DATA_TYPE']),
                        'default' => in_array($row['COLUMN_DEFAULT'], array('NULL', 'CURRENT_TIMESTAMP')) ? null : $row['COLUMN_DEFAULT'],
                        'blob' => preg_match('/(TEXT|BLOB)/', $row['DATA_TYPE']),
                    );
                }
                return $meta;
        }
    }
    /**
     * Returns a list of foreign keys for a table.
     */
    public function getForeignKeys($table) {
        switch ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
                if (strpos(".", $table) === false) {
                    $table = $this->lookupQualifiedTablename($table);
                }
            case 'mysql':
                $meta = array();
                foreach ($this->loadKeys() as $info) {
                    if ($info['table_name'] === $table) {
                        $meta[] = array(
                            'table' => $info['table_name'],
                            'column' => $info['column_name'],
                            'referenced_table' => $info['referenced_table_name'],
                            'referenced_column' => $info['referenced_column_name'],
                        );
                    }
                }
                return $meta;
            case 'sqlite':
                $sql = "PRAGMA foreign_key_list(" . $this->connection->quoteName($table) . ")";
                $result = $this->connection->query($sql);
                $result->setFetchMode(PDO::FETCH_ASSOC);
                $meta = array();
                foreach ($result as $row) {
                    $meta[] = array(
                        'table' => $table,
                        'column' => $row['from'],
                        'referenced_table' => $row['table'],
                        'referenced_column' => $row['to'],
                    );
                }
                return $meta;
                break;
            default:
                throw new pdoext_MetaNotSupportedException();
        }
    }
    /**
     * Returns a list of foreign keys that refer a table.
     */
    public function getReferencingKeys($table) {
        switch ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
                if (strpos(".", $table) === false) {
                    $table = $this->lookupQualifiedTablename($table);
                }
            case 'mysql':
                $meta = array();
                foreach ($this->loadKeys() as $info) {
                    if ($info['referenced_table_name'] === $table) {
                        $meta[] = array(
                            'table' => $info['table_name'],
                            'column' => $info['column_name'],
                            'referenced_table' => $info['referenced_table_name'],
                            'referenced_column' => $info['referenced_column_name'],
                        );
                    }
                }
                return $meta;
            case 'sqlite':
                $meta = array();
                foreach ($this->getTables() as $tbl) {
                    if ($tbl != $table) {
                        foreach ($this->getForeignKeys($tbl) as $info) {
                            if ($info['referenced_table'] == $table) {
                                $meta[] = $info;
                            }
                        }
                    }
                }
                return $meta;
            default:
                throw new pdoext_MetaNotSupportedException();
        }
    }
    function belongsTo($tablename) {
        if (!isset($this->belongs_to[$tablename])) {
            $this->belongs_to[$tablename] = array();
            foreach ($this->getForeignKeys($tablename) as $info) {
                $name = preg_replace('/_id$/', '', $info['column']);
                $this->belongs_to[$tablename][$name] = $info;
            }
        }
        return $this->belongs_to[$tablename];
    }
    function hasMany($tablename) {
        if (!isset($this->has_many[$tablename])) {
            $this->has_many[$tablename] = array();
            foreach ($this->getReferencingKeys($tablename) as $info) {
                $name = $info['table'];
                $this->has_many[$tablename][$name] = $info;
            }
        }
        return $this->has_many[$tablename];
    }

    protected function lookupQualifiedTablename($tableName) {
        $stmt = $this->connection->queryx(
            "SELECT concat(nspname, '.', relname) FROM pg_class JOIN pg_namespace ON pg_class.relnamespace = pg_namespace.oid WHERE pg_class.oid = :tablename::regclass",
            array(":tablename" => $tableName)
        );
        return $stmt->fetchColumn();
    }

    /**
     * @internal
     */
    protected function loadKeys() {
        if (!isset($this->keys)) {
            switch ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)) {
                case 'mysql':
                    $sql = "SELECT TABLE_NAME AS `table_name`, COLUMN_NAME AS `column_name`, REFERENCED_COLUMN_NAME AS `referenced_column_name`, REFERENCED_TABLE_NAME AS `referenced_table_name`
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
  AND REFERENCED_TABLE_SCHEMA = DATABASE()";
                    break;
                case 'pgsql':
                    $sql = "SELECT conname as foreign_key_name,
                           concat(pg_namespace_con.nspname, '.', pg_class_con.relname) as table_name,
                           conatt.attname as column_name,
                           concat(pg_namespace_con.nspname, '.', pg_class.relname) as referenced_table_name,
                           pg_attribute.attname as referenced_column_name
                      FROM pg_constraint
                CROSS JOIN generate_series(1, array_length(conkey,1))
                      JOIN pg_class as pg_class_con
                        ON pg_class_con.oid = pg_constraint.conrelid
                      JOIN pg_namespace AS pg_namespace_con
                        ON pg_namespace_con.oid = pg_class_con.relnamespace
                      JOIN pg_class
                        ON pg_class.oid = pg_constraint.confrelid
                      JOIN pg_namespace AS pg_namespace
                        ON pg_namespace.oid = pg_class.relnamespace
                      JOIN pg_attribute
                        ON pg_attribute.attrelid = pg_constraint.confrelid
                       AND pg_attribute.attnum = pg_constraint.confkey[generate_series]
                      JOIN pg_attribute conatt
                        ON conatt.attrelid = pg_constraint.conrelid
                       AND conatt.attnum = pg_constraint.conkey[generate_series]
                     WHERE contype = 'f'
                       AND pg_attribute.attisdropped = false
                  ORDER BY 1, 2, 3, 4";
                    break;
                default:
                    throw new pdoext_MetaNotSupportedException();
            }
            $result = $this->connection->query($sql);
            $result->setFetchMode(PDO::FETCH_ASSOC);
            $this->keys = array();
            foreach ($result as $row) {
                $this->keys[] = $row;
            }
        }
        return $this->keys;
    }
}
