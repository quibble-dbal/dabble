<?php

/**
 * PostgreSQL-specific database abstraction layer.
 *
 * @package Dabble
 * @subpackage Adapter
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2015
 */

namespace Dabble\Adapter;

use Dabble\Adapter as DabbleAdapter;

use monolyth\utils;
use monolyth\Config;
use PDOStatement;

/**
 * PostgreSQL database abstraction class.
 * Database abstractions are usually called through DB::method or
 * DB::i[nstance]($name)->method.
 */
class Postgresql extends DabbleAdapter
{
    private $host, $username, $password, $database, $handle, $limit, $offset;

    public function __toString()
    {
        return $this->database;
    }

    public function log($msg)
    {
        dump($msg);
    }

    public function any($key, $values, &$bind)
    {
        $els = [];
        if (!is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $value) {
            $els[] = sprintf(
                '%s = ANY(%s)',
                implode('', $this->values([$value])),
                $key
            );
        }
        return '('.implode(' OR ', $els).')';
    }

    public function dump()
    {
        print "<ul>\n";
        foreach (static::$queries as $query) {
            print "<li>$query</li>\n";
        }
        print "</ul>\n";
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
        return sprintf("'%d %s'::interval", $amount, $what);
    }
    
    /**
     * Return an array of field-definitions for a given table.
     *
     * @param string $table The table to describe.
     * @return array Array of definitions.
     */
    public function describe($table)
    {
        static $tablefields = [], $statement = null;
        $this->connect();
        if (isset($tablefields[$table])) {
            return $tablefields[$table];
        }
        $fields = [];
        if (!isset($statement)) {
            $statement = $this->prepare(
                "SELECT f.attnum AS number,
                        f.attname AS name,
                        f.attnum,
                        f.attnotnull AS notnull,
                        pg_catalog.format_type(f.atttypid,f.atttypmod) AS type,
                        CASE WHEN p.contype = 'p' THEN 't' ELSE 'f' END
                            AS primarykey,
                        CASE WHEN p.contype = 'u' THEN 't' ELSE 'f' END
                            AS uniquekey,
                        CASE WHEN p.contype = 'f' THEN g.relname END
                            AS foreignkey,
                        CASE WHEN p.contype = 'f' THEN p.confkey END
                            AS foreignkey_fieldnum,
                        CASE WHEN p.contype = 'f' THEN p.conkey END
                            AS foreignkey_connnum,
                        CASE WHEN f.atthasdef = 't' THEN d.adsrc END
                            AS default
                    FROM pg_attribute f
                        JOIN pg_class c ON c.oid = f.attrelid
                        JOIN pg_type t ON t.oid = f.atttypid
                        LEFT JOIN pg_attrdef d ON d.adrelid = c.oid
                                              AND d.adnum = f.attnum
                        LEFT JOIN pg_namespace n ON n.oid = c.relnamespace
                        LEFT JOIN pg_constraint p ON p.conrelid = c.oid
                                                 AND f.attnum = ANY(p.conkey)
                        LEFT JOIN pg_class AS g ON p.confrelid = g.oid
                    WHERE c.relkind = 'r'::char AND
                          n.nspname = ? AND
                          c.relname = ? AND
                          f.attnum > 0 ORDER BY number"
            );
        }
        $statement->execute(['public', $table]);
        if (!count($statement)) {
            throw new TableDoesntExist_Exception($table);
        }
        $idx = 0;
        while (false !== ($field = $statement->fetch(self::FETCH_ASSOC))) {
            ++$idx;
            $type = $field['type'];
            if (!strpos($type, '(')) {
                $type .= '()';
            }
            list($subtype, $params) = explode('(', $type);
            $params = substr($params, 0, -1);
            $data =& $fields[$field['name']];
            $data = [];
            switch ($subtype) {
                case 'varchar':
                case 'character varying':
                    $type = 'varchar';
                    $data['size'] = $params;
                    break;
                case 'inet':
                    $type = 'varchar';
                    $data['size'] = 39;
                    break;
                case 'bigint':
                case 'mediumint':
                case 'tinyint':
                case 'double precision':
                case 'float':
                case 'integer':
                    $type = 'numeric';
                    if ($field['primarykey'] == 't'
                        && substr($field['default'], 0, 7) == 'nextval'
                    ) {
                        $type = 'serial';
                    }
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                case 'timestamp with time zone':
                    $type = 'date';
                    break;
                case 'longtext': case 'mediumtext': case 'smalltext':
                case 'text':
                    $type = 'text';
                    break;
                case 'bytea':
                    $type = 'file';
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
                default:
                    var_dump($field);
                    die();
            }
            $data['type'] = $type;
            $data['null'] = $field['notnull'] == 't';
            $data['is_primary_key'] = $field['primarykey'] == 't';
            $data['has_foreign_key'] = false;
            if ($field['foreignkey']) {
                // Multi-column foreign keys are passed by Postgres in a
                // f***ed up manner, pardon my French. This parsing is required
                // to find the column actually associated with this one.
                $foreign = self::stringToArray($field['foreignkey_fieldnum']);
                $local = self::stringToArray($field['foreignkey_connnum']);
                foreach ($local as $key => $value) {
                    if ($value == $idx) {
                        $data['has_foreign_key'] = $field['foreignkey']
                            .'.'.$foreign[$key];
                    }
                }
            }
        }
        $tablefields[$table] = $fields;
        return $fields;
    }

    /**
     * Return all foreign key relations for $table.
     *
     * @param string $table The table whose foreign keys we want.
     * @return array Array of foreign keys ([$table.]field => table.field).
     */
    public function foreignKeys($table)
    {
        $fks = [];
        foreach (explode(' join ', strtolower($table)) as $part) {
            $tbl = trim(array_shift(explode(' ', trim($part))));
            $fields = $this->describe($tbl);
            foreach ($fields as $name => $field) {
                if ($field['has_foreign_key']) {
                    list($tbl, $idx) = explode('.', $field['has_foreign_key']);
                    $q = $this->describe($tbl);
                    $keys = array_keys($q);
                    $fks["$table.$name"] = "$tbl.{$keys[$idx - 1]}";
                }
            }
        }
        return $fks;
    }

    public function random()
    {
        return 'RANDOM()';
    }

    public function rows($table, $fields, $where = null, $options = null)
    {
        unset($this->limit);
        return parent::rows($table, $fields, $where, $options);
    }

    /**
     * The native driver does not convert PostgreSQL arrays to PHP arrays.
     * So, this method does. :)
     *
     * @param string $string A database-field containing an array.
     * @return array A PHP array of its values.
     */
    public static function stringToArray($string)
    {
        if (!preg_match("@^{.*?}$@", $string)) {
            return $string;
        }
        $parts = explode(',', substr($string, 1, -1));
        foreach ($parts as &$part) {
            $part = self::stringToArray($part);
        }
        return $parts;
    }
}

