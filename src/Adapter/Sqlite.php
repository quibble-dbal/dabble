<?php

/**
 * Database abstraction layer for SqLite.
 *
 * @package Dabble
 * @subpackage Adapter
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2015
 */

namespace Dabble\Adapter;

use Dabble\Adapter as DabbleAdapter;

use monolyth\utils;
use monolyth\core;
use monolyth\adapter as base;
use monolyth\Config;
use PDOException;
use PDOStatement;
use ErrorException;

/** SqLite-abstraction class. */
class Sqlite extends DabbleAdapter
{
    public function fieldnames($resource)
    {
        $fields = [];
        for ($i = 0; $i < mysql_num_fields($resource); $i++) {
            $fields[] = mysql_field_name($resource, $i);
        }
        return $fields;
    }

    public function value($value, &$bind)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return parent::value($value, $bind);
    }

    public function call($procedure)
    {
        $command = "CALL $procedure(%s)";
        $args = func_get_args();
        array_shift($args);
        foreach ($args as &$arg) {
            $arg = $this->value($arg);
        }
        $command = sprintf($command, implode(', ', $args));
        return $this->execute($command);
    }

    public function numRowsTotal(PDOStatement $result, &$bind)
    {
        $this->connect();
        $statement = $this->pdo->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        return $statement->fetchColumn();
    }

    public function interval($quantity, $amount)
    {
        $what = null;
        switch ($quantity) {
            case self::SECOND: $what = 'second'; break;
            case self::MINUTE: $what = 'minute'; break;
            case self::HOUR: $what = 'hour'; break;
            case self::DAY: $what = 'day'; break;
            case self::WEEK: $what = 'week'; break;
            case self::MONTH: $what = 'month'; break;
            case self::YEAR: $what = 'year'; break;
        }
        return sprintf("interval %d %s", $amount, $what);
    }

    public function tables()
    {
        $tables = [];
        try {
            $q = $this->results($this->execute("SHOW TABLES FROM {$this->database}"));
        } catch (Exception $e) {
            return $tables;
        }
        foreach ($q as $row) {
            foreach ($row as $field) {
                $tables[] = $field;
                break;
            }
        }
        return $tables;
    }

    /**
     * Return an array of field-definitions for a given table.
     *
     * @param string $table The table to describe.
     * @return array Array of definitions.
     */
    public function describe($table)
    {
        $fields = [];
        try {
            foreach ($this->query("SHOW COLUMNS FROM $table") as $field) {
                $type = $field['Type'];
                if (!strpos($type, '(')) {
                    $type .= '()';
                }
                list($subtype, $params) = preg_split('@( |\()@', $type, 2);
                $params = substr($params, 0, -1);
                $data =& $fields[$field['Field']];
                $data = [];
                switch ($subtype) {
                    case 'varchar':
                        $type = 'varchar';
                        $data['size'] = $params;
                        break;
                    case 'bigint':
                    case 'mediumint':
                    case 'tinyint':
                    case 'float':
                    case 'int':
                        $type = 'numeric';
                        if ($field['Extra'] == 'auto_increment') {
                            $type = 'serial';
                        }
                        break;
                    case 'datetime': case 'timestamp':
                        $type = 'datetime';
                        break;
                    case 'date': $type = 'date'; break;
                    case 'longtext': case 'mediumtext': case 'smalltext':
                    case 'tinytext': case 'text':
                        $type = 'text';
                        break;
                    case 'enum':
                        $type = 'select';
                        $params = explode(',', $params);
                        foreach ($params as $key => $param) {
                            $params[$key] = preg_replace(
                                "@^'(.*?)'$@",
                                '$1',
                                trim($param)
                            );
                        }
                        $allowed = [];
                        foreach ($params as $param) {
                            $allowed[$param] = $param;
                        }
                        $data['allowed'] = $allowed;
                        break;
                }
                $data['type'] = $type;
                $data['null'] = strtolower($field['Null']) == 'yes' ? true : false;
                $data['is_primary_key'] = strtolower($field['Key']) == 'pri';
            }
            return $fields;
        } catch (PDOException $e) {
            throw new TableDoesntExist_Exception($table);
        }
    }

    /**
     * Return all foreign key relations for $table.
     *
     * @param string $table The table whose foreign keys we want.
     * @return array Array of foreign keys ([$table.]field => table.field).
     */
    public function foreignKeys($table)
    {
        $tbls = [];
        foreach (explode(' join ', strtolower($table)) as $part) {
            $tbls[] = "'".array_shift(explode(' ', $part))."'";
        }
        $table = implode(', ', $tbls);
        $config = Config::get('database');
        foreach ($config as $name => $db) {
            if ($name{0} == '_') {
                continue;
            }
            if ($db == $config->_current) {
                break;
            }
        }
        try {
            $fks = [];
            foreach ($this->query(
                "SELECT CONCAT_WS('.', u.table_name, u.column_name) AS field,
                    CONCAT_WS('.', u.referenced_table_name,
                        u.referenced_column_name) AS refs
                 FROM information_schema.referential_constraints AS c
                     INNER JOIN information_schema.key_column_usage AS u
                     USING(constraint_schema, constraint_name)
                 WHERE c.constraint_schema = '$name'
                    AND u.table_name IN ($table)"
            ) as $row) {
                $fks[$row['field']] = $row['refs'];
            }
            return $fks;
        } catch (ErrorException $e) {
            return [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Return all parent relations for $table. A parent relation is defined
     * as a table which has a foreign key referencing $table.
     *
     * @param string $table The table whose parent relations we want.
     * @return array Array of parent relations ([$table.]field => table.field).
     */
    public function parentKeys($table)
    {
        try {
            $fks = [];
            foreach ($this->query(
                "SELECT u.table_schema AS 'Schema', u.table_name AS 'Table',
                    u.column_name AS 'Key',
                    u.referenced_table_schema AS 'Parent Schema',
                    u.referenced_table_name AS 'Parent table',
                    u.referenced_column_name AS 'Parent key'
                 FROM information_schema.table_constraints AS c
                 INNER JOIN information_schema.key_column_usage AS u
                 USING(constraint_schema, constraint_name)
                 WHERE c.constraint_type = 'FOREIGN KEY'
                    AND c.table_schema = '{$this->database}'
                    AND u.referenced_table_name = '$table'
                    ORDER BY u.table_schema,u.table_name,u.column_name"
            ) as $row) {
            }
        } catch (ErrorException $e) {
            return [];
        } catch (PDOException $e) {
        }
    }

    /**
     * Retrieve a paginated resultset from the database.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return mixed An array or resultsets, or null.
     * @throw monolyth\adapter\sql\NoResults_Exception when 0 rows were found.
     */
    public function pages($table, $fields, $where = null,
        $options = null, $output = null
    )
    {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $old = $fields[0];
        if (isset($options['limit'])) {
            $fields[0] = "SQL_CALC_FOUND_ROWS {$fields[0]}";
            $limit = $options['limit'];
        }
        try {
            return parent::pages(
                $table,
                $fields,
                $where,
                $options,
                $output
            );
        } catch (CalcRowsFailed_Exception $e) {
            $fields[0] = $old;
            if (!isset($output)) {
                return parent::rows($table, $fields, $where, $options);
            }
            return parent::getObjects(
                $table,
                $fields,
                $where,
                $options,
                $output
            );
        }
    }

    public function getPaginated()
    {
        return call_user_func_array([$this, 'pages'], func_get_args());
    }

    public function random()
    {
        return 'RAND()';
    }

    public function upsert($table, array $fields)
    {
        $sql = preg_replace(
            "@^INSERT@m",
            "REPLACE",
            $this->insertQuery($table, $fields)
        );
        if (!($result = $this->execute($sql)
            and $affectedRows = $this->affectedRows($result)
        )) {
            throw new UpsertNone_Exception($sql);
        }
        return true;
    }

    public function __toString()
    {
        return $this->id;
    }
}

