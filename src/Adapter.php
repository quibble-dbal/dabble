<?php

/**
 * A generic class all database classes involving SQL should extend.
 *
 * @package Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015
 */

namespace Dabble;

use PDO;
use PDOException;
use PDOStatement;
use ArrayObject;

abstract class Adapter extends PDO
{
    /**
     * Constants for aiding in interval statements.
     * {{{
     */
    const YEAR = 1;
    const MONTH = 2;
    const WEEK = 3;
    const DAY = 4;
    const HOUR = 5;
    const MINUTE = 6;
    const SECOND = 7;
    /** }}} */

    protected $translevel = 0;
    private $connectionSettings = [];
    private $connected = false;

    public function __construct($d, $u = null, $p = null, array $o = [])
    {
        $o[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $this->connectionSettings = compact('d', 'u', 'p', 'o');
    }

    /**
     * Opens a just-in-time database connection associated with this adapter.
     * This allows you to define as many databases as you want in a central file
     * without necessarily worrying about overhead (e.g. lots of related sites).
     *
     * @throws Dabble\Adapter\ConnectionFailedException if the database is
     *                                                  unavailable.
     */
    protected function connect()
    {
        if ($this->connected) {
            return;
        }
        try {
            extract($this->connectionSettings);
            parent::__construct($d, $u, $p, $o);
            $this->connected = true;
        } catch (PDOException $e) {
            throw new Adapter\ConnectionFailedException($e->getMessage());
        }
    }

    public function reconnect()
    {
        $this->connected = false;
        $this->connect();
    }

    /**
     * Internal helper to correctly formate error messages.
     *
     * @param string $msg The original error message.
     * @param array $bind Array of bound values.
     * @return string Formatted error message.
     */
    protected function error($msg, array $bind = [])
    {
        foreach ($bind as $key => $value) {
            if (is_null($value)) {
                $value = 'NULL';
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $msg .= "$key => $value\n";
        }
        return $msg;
    }

    /**
     * Expose all PDO's original methods, optionally with additional
     * Dabble-specific functionality.
     *
     * {{{
     */
    public function beginTransaction()
    {
        $this->connect();
        if (!$this->translevel++) {
            return parent::beginTransaction();
        }
    }

    public function commit()
    {
        $this->connect();
        if ($this->translevel-- == 1) {
            return parent::commit();
        }
    }

    public function errorCode()
    {
        $this->connect();
        return parent::errorCode();
    }

    public function errorInfo()
    {
        $this->connect();
        return parent::errorInfo();
    }

    public function exec($statement)
    {
        $this->connect();
        return parent::exec($statement);
    }

    public function getAttribute($attribute)
    {
        $this->connect();
        return parent::getAttribute($attribute);
    }

    public function inTransaction()
    {
        return $this->translavel;
    }

    public function lastInsertId($name = null)
    {
        $this->connect();
        return parent::lastInsertId($name);
    }

    public function prepare($statement, array $driver_options = [])
    {
        $this->connect();
        return parent::prepare($statement, $driver_options);
    }

    public function query($statement)
    {
        $this->connect();
        return parent::query($statement);
    }

    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        $this->connect();
        if (is_object($string) && $string instanceof Raw) {
            return "$string";
        }
        return parent::quote($string, $parameter_type);
    }

    public function rollback()
    {
        $this->connect();
        if ($this->translevel-- == 1) {
            return parent::rollback();
        }
    }

    public function setAttribute($attribute, $value)
    {
        $this->connect();
        return parent::setAttribute($attribute, $value);
    }
    /**
     * }}}
     */

    public function flush()
    {
        $this->connect();
        $this->cache = [];
    }

    /**
     * Select all rows from a table, PDOStatement::fetchAll-style.
     *
     * @param string $table The table(s) to query.
     * @param mixed $fields The field (column) to query.
     * @param mixed $where The where-clause.
     * @param mixed $options The options (limit, offset etc.).
     * @return array Array containing all found rows.
     * @throws Dabble\Query\SelectException when no rows found.
     * @throws Dabble\Query\SqlException on error.
     */
    public function fetchAll($table, $fields, $where = [], $options = [])
    {
        $results = $this->select($table, $fields, $where, $options);
        $return = [];
        foreach ($results() as $row) {
            $return[] = $row;
        }
        return $return;
    }

    /**
     * Select rows from a table.
     *
     * @param string $table The table(s) to query.
     * @param mixed $fields The field (column) to query.
     * @param mixed $where The where-clause.
     * @param mixed $options The options (limit, offset etc.).
     * @return function A lambda allowing you to access the found rows.
     * @throws Dabble\Query\SelectException when no rows found.
     * @throws Dabble\Query\SqlException on error.
     */
    public function select($table, $fields, $where = [], $options = [])
    {
        $bind = [];
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s %s",
            implode(', ', $fields),
            $table,
            $this->where($where, $bind),
            $this->options($options, $bind)
        );
        try {
            $key = serialize([$sql, $bind]);
            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }
        } catch (PDOException $e) {
        }
        if (!isset($this->prepared[$sql])) {
            $this->connect();
            $this->prepared[$sql] = $this->prepare($sql);
        }
        try {
            $stmt = $this->prepared[$sql];
            $stmt->execute($bind);
        } catch (PDOException $e) {
            throw new Query\SqlException(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                1,
                $e
            );
        }
        if (false === ($first = $stmt->fetch(PDO::FETCH_ASSOC))) {
            throw new Query\SelectException($sql);
        }
        return function () use ($stmt, &$first) {
            if ($first) {
                $yield = $first;
                $first = false;
                yield $yield;
            }
            while (false !== $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                yield $row;
            }
            return;
        };
    }

    /**
     * Retrieve a single row from the database.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return array An array containing the result.
     * @throws Dabble\Query\SelectException when no row was found.
     * @throws Dabble\Query\SqlException on error.
     */
    public function fetch($table, $fields, $where = null, $options = [])
    {
        $this->connect();
        $options['limit'] = 1;
        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }
        $result = $this->select($table, $fields, $where, $options);
        foreach ($result() as $row) {
            return $row;
        }
    }

    /**
     * Retrieve a single value from a single column.
     *
     * @param string $table The table(s) to query.
     * @param string $field The field (column) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return mixed A scalar containing the result, or null.
     * @throws Dabble\Query\SelectException when no row was found.
     * @throws Dabble\Query\SqlException on error.
     */
    public function column($table, $field, $where = null, $options = null)
    {
        return array_shift($this->fetch($table, $field, $where, $options));
    }

    /**
     * Retrieve a count from a table.
     *
     * @param string $table The table(s) to query.
     * @param array $where An SQL where-array.
     * @return integer The number of matched rows.
     * @throws Dabble\Query\SqlException on error.
     */
    public function count($table, $where = null)
    {
        return $this->column($table, 'COUNT(*)', $where);
    }

    /**
     * Insert a row into the database.
     *
     * @param string $table The table to insert into.
     * @param array $fields Array of Field => value pairs to insert.
     * @return mixed The last inserted serial, or 0 or true if none found.
     * @throws Dabble\Query\InsertException if no rows were inserted.
     * @throws Dabble\Query\SqlException on error.
     */
    public function insert($table, array $fields)
    {
        $bind = [];
        $use = [];
        foreach ($fields as $name => $field) {
            if (is_null($field)) {
                continue;
            }
            $use[$name] = $field;
        }
        if (!$use) {
            throw new Query\InsertException(
                "No fields to bind; did you pass only NULL values?"
            );
        }
        $fields = $use;
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            str_replace("'", '', implode(', ', array_keys($fields))),
            implode(', ', $this->values($fields, $bind))
        );
        $this->connect();
        try {
            $statement = $this->prepare($sql);
            $statement->execute($bind);
        } catch (PDOException $e) {
            throw new Query\SqlException(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                2,
                $e
            );
        }
        if (!(($affectedRows = $statement->rowCount()) && $affectedRows)) {
            $info = $statement->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new Query\InsertException($this->error($msg, $bind));
        }
        return $affectedRows;
    }
    
    /**
     * Update one or more rows in the database.
     *
     * @param string $table The table to update.
     * @param array $fields Array Field => value pairs to update.
     * @param array $where Array of where statements to limit updates.
     * @return integer The number of affected (updated) rows.
     * @throws Dabble\Query\UpdateException if no rows were updated.
     * @throws Dabble\Query\SqlException on error.
     */
    public function update($table, array $fields, $where, $options = null)
    {
        $bind = [];
        foreach ($fields as $key => &$value) {
            if (is_array($value)) {
                $value = call_user_func(function($value) {
                    $new = 0;
                    foreach ($value as $val) {
                        if (strlen($val) && !is_numeric($val)) {
                            return $value;
                        }
                        $new |= $val;
                    }
                    return $new;
                }, $value);
            }
            if (!is_numeric($key)) {
                $value = $key.' = '.$this->value($value, $bind);
            }
        }
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s %s",
            $table,
            implode(', ', $fields),
            $this->where($where, $bind),
            $this->options($options, $bind)
        );
        try {
            $this->connect();
            $statement = $this->prepare($sql);
            $statement->execute($bind);
        } catch (PDOException $e) {
            throw new Query\SqlException(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                3,
                $e
            );
        }
        if (!(($affectedRows = $statement->rowCount()) && $affectedRows)) {
            $info = $statement->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new Query\UpdateException($this->error($msg, $bind), 1);
        }
        return $affectedRows;
    }

    /**
     * Delete a row from the database.
     *
     * @param string $table The table to delete from.
     * @param array $where Array of where statements to limit deletes.
     * @return int The number of deleted rows.
     * @throws Dabble\Query\DeleteException if no rows were deleted.
     * @throws Dabble\Query\SqlException on error.
     */
    public function delete($table, array $where)
    {
        $bind = [];
        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            $this->where($where, $bind)
        );
        $this->connect();
        try {
            $statement = $this->prepare($sql);
            $statement->execute($bind);
        } catch (PDOException $e) {
            throw new Query\SqlException(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                4,
                $e
            );
        }
        if (!(($affectedRows = $statement->rowCount()) && $affectedRows)) {
            $info = $statement->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new Query\DeleteException($this->error($msg, $bind));
        }
        return $affectedRows;
    }

    public function numRowsTotal(PDOStatement $result, &$bind)
    {
        $this->connect();
        $sql = $result->queryString;
        $sql = preg_replace('/SELECT.*?FROM/si', 'SELECT COUNT(*) FROM', $sql);
        $sql = preg_replace('/(LIMIT|OFFSET)\s+\d+/si', '', $sql);
        $sql = preg_replace('/ORDER\s+BY.*?$/si', '', $sql);
        $statement = $this->prepare($sql);
        $statement->execute($bind);
        return $statement->fetchColumn();
    }

    public function now($string = false)
    {
        if (!$string) {
            return ['NOW()'];
        }
        return 'NOW()';
    }

    public function datenull()
    {
        return null;
    }

    public function values($array, &$bind)
    {
        foreach ($array as &$value) {
            $value = $this->value($value, $bind);
        }
        return $array;
    }

    public function value($value, &$bind)
    {
        if (is_null($value)) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value instanceof ArrayObject) {
            $value = (array)$value;
        }
        if (is_array($value)) { // literal
            return array_shift($value);
        }
        if (is_object($value)) {
            $value = "$value";
        }
        $bind[] = $value;
        return '?';
    }

    public function where($array, array &$bind, $seperator = 'AND')
    {
        $this->connect();
        if (!$array) {
            return '(1=1)';
        }
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $array[$key] = $this->where(
                    $value,
                    $bind,
                    $seperator == 'AND' ? 'OR' : 'AND'
                );
            } elseif (is_array($value)) {
                $keys = array_keys($value);
                $mod = array_shift($keys);
                switch (strtoupper($mod)) {
                    case 'BETWEEN':
                        $vals = array_shift($value);
                        $array[$key] = sprintf(
                            "($key BETWEEN %s AND %s)",
                            $this->quote(array_shift($vals)),
                            $this->quote(array_shift($vals))
                        );
                        break;
                    case 'IN':
                    case 'NOT IN':
                        $array[$key] = $this->in(
                            $key,
                            $value[$mod],
                            strtoupper($mod),
                            $bind
                        );
                        break;
                    case 'ANY':
                        $array[$key] = $this->any(
                            $key,
                            array_unique($value[$mod]),
                            $bind
                        );
                        break;
                    case 'LIKE':
                        $array[$key] = sprintf(
                            "(%s LIKE %s OR %s LIKE %s OR %s LIKE %s)",
                            $key,
                            $this->quote("%{$value[$mod]}"),
                            $key,
                            $this->quote("{$value[$mod]}%"),
                            $key,
                            $this->quote("%{$value[$mod]}%")
                        );
                        break;
                    default:
                        $val = array_shift($value);
                        $array[$key] = sprintf(
                            '%s %s %s',
                            $key,
                            $this->operator($val, $mod),
                            $this->value($val, $bind)
                        );
                }
            } else {
                $array[$key] = sprintf(
                    '%s %s %s',
                    $key,
                    $this->operator($value),
                    $this->value($value, $bind)
                );
            }
        }
        return '('.implode(" $seperator ", $array).')';
    }

    public function in($key, $values, $operator, &$bind)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        $value = array_unique($values);
        return sprintf(
            '%s %s (%s)',
            $key,
            $operator,
            implode(', ', $this->values($values, $bind))
        );      
    }

    public function operator($value, $operator = '=')
    {
        if ($value === null) {
            return $operator == '=' ? 'IS' : 'IS NOT';
        }
        if ($operator === '!') {
            return '<>';
        }
        if (is_numeric($operator)) {
            return '=';
        }
        return $operator;
    }
    
    public function options($myoptions, &$bind)
    {
        if (!$myoptions) {
            return '';
        }
        $options = [];
        foreach ($myoptions as $key => $value) {
            $options[strtoupper($key)] = $value;
        }
        $myoptions = [];
        if (isset($options['GROUP'])) {
            if (!is_array($options['GROUP'])) {
                $options['GROUP'] = [$options['GROUP']];
            }
            $myoptions[] = sprintf(
                "GROUP BY %s",
                implode(', ', $options['GROUP'])
            );
        }
        if (isset($options['HAVING'])) {
            $myoptions[] = sprintf(
                "HAVING %s",
                $this->where($options['HAVING'], $bind)
            );
        }
        if (isset($options['ORDER'])) {
            $tmp = [];
            if (!is_array($options['ORDER'])) {
                $myoptions[] = "ORDER BY {$options['ORDER']}";
            } else {
                foreach ($options['ORDER'] as $order) {
                    if (!is_array($order)) {
                        $tmp[] = $order;
                        continue;
                    }
                    $dir = array_shift($dir = array_keys($order));
                    $col = array_shift($order);
                    if (!is_array($col)) {
                        $col = [$col];
                    }
                    foreach ($col as $onecol) {
                        $tmp[] = sprintf(
                            '%s %s',
                            $onecol,
                            strtoupper($dir)
                        );
                    }
                }
                $myoptions[] = sprintf(
                    "ORDER BY %s",
                    implode(', ', $tmp)
                );
            }
        }
        if (isset($options['LIMIT'])) {
            $myoptions[] = sprintf(
                "LIMIT %s",
                $options['LIMIT']
            );
        }
        if (isset($options['OFFSET'])) {
            $myoptions[] = sprintf(
                "OFFSET %d",
                $options['OFFSET']
            );
        }
        return implode(' ', $myoptions);
    }
}

