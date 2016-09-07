<?php

/**
 * A generic class all database classes involving SQL should extend.
 *
 * @package Quibble\Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016
 */

namespace Quibble\Dabble;

use Quibble\Dabble\Where;
use Quibble\Dabble\Options;
use Quibble\Dabble\Select;
use Quibble\Dabble\Insert;
use Quibble\Dabble\Update;
use Quibble\Dabble\Delete;
use Quibble\Dabble\Raw;
use PDO;
use PDOException;
use PDOStatement;

abstract class Adapter extends PDO
{
    use Value {
        Value::value as _value;
    }
    use Normalize;

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

    protected $transactionLevel = 0;
    private $connectionSettings = [];
    private $connected = false;

    public function __construct(
        $dsn,
        $username = null,
        $password = null,
        array $options = []
    ) {
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $this->connectionSettings = compact(
            'dsn',
            'username',
            'password',
            'options'
        );
    }

    public function id()
    {
        return $this->connectionSettings['dsn']
            .$this->connectionSettings['username'];
    }

    /**
     * Opens a just-in-time database connection associated with this adapter.
     * This allows you to define as many databases as you want in a central file
     * without necessarily worrying about overhead (e.g. lots of related sites).
     *
     * @throws Quibble\Dabble\ConnectionFailedException if the database is
     *  unavailable.
     */
    public function connect()
    {
        if ($this->connected) {
            return;
        }
        try {
            extract($this->connectionSettings);
            parent::__construct($dsn, $username, $password, $options);
            $this->connected = true;
        } catch (PDOException $e) {
            throw new ConnectionFailedException($e->getMessage());
        }
    }

    public function reconnect()
    {
        $this->connected = false;
        $this->connect();
    }

    /**
     * Expose all PDO's original methods, optionally with additional
     * Quibble\Dabble-specific functionality.
     *
     * {{{
     */
    public function beginTransaction()
    {
        $this->connect();
        if (!$this->transactionLevel++) {
            return parent::beginTransaction();
        }
    }

    public function commit()
    {
        $this->connect();
        if ($this->transactionLevel-- == 1) {
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
        return $this->transactionLevel;
    }

    public function lastInsertId($name = null)
    {
        $this->connect();
        return parent::lastInsertId($name);
    }

    public function prepare($statement, $driver_options = null)
    {
        $this->connect();
        $driver_options = $driver_options ?: [];
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
        if ($this->transactionLevel-- == 1) {
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
     * Select rows from a table.
     *
     * @param string $table The table(s) to query.
     * @param mixed $fields The field (column) to query.
     * @param mixed $where The where-clause.
     * @param mixed $options The options (limit, offset etc.).
     * @return function A lambda allowing you to access the found rows.
     * @throws Quibble\Dabble\SelectException when no rows found.
     * @throws Quibble\Dabble\SqlException on error.
     */
    public function select($table, $fields, $where = [], $options = [])
    {
        if (is_scalar($fields)) {
            $fields = explode(',', $fields);
        }
        $query = new Select(
            $this,
            $table,
            $fields,
            new Where($where),
            new Options($options)
        );
        $res = $query->execute();
        if (!$res->valid()) {
            throw new SelectException("$query");
        }
        return $res;
    }

    /**
     * Select all rows from a table, PDOStatement::fetchAll-style.
     *
     * @param string $table The table(s) to query.
     * @param mixed $fields The field (column) to query.
     * @param mixed $where The where-clause.
     * @param mixed $options The options (limit, offset etc.).
     * @return array Array containing all found rows.
     * @throws Quibble\Dabble\SelectException when no rows found.
     * @throws Quibble\Dabble\SqlException on error.
     */
    public function fetchAll($table, $fields, $where = [], $options = [])
    {
        if (is_scalar($fields)) {
            $fields = explode(',', $fields);
        }
        $query = new Select(
            $this,
            $table,
            $fields,
            new Where($where),
            new Options($options)
        );
        $stmt = $this->prepare($query->__toString());
        $stmt->execute($query->getBindings());
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$results) {
            throw new SelectException($stmt->queryString);
        }
        array_walk($results, [$this, 'normalize']);
        return $results;
    }

    /**
     * Retrieve a single row from the database.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return array An array containing the result.
     * @throws Quibble\Dabble\SelectException when no row was found.
     * @throws Quibble\Dabble\SqlException on error.
     */
    public function fetch($table, $fields, $where = null, $options = [])
    {
        $options['limit'] = 1;
        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }
        $result = $this->select($table, $fields, $where, $options);
        foreach ($result as $row) {
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
     * @throws Quibble\Dabble\SelectException when no row was found.
     * @throws Quibble\Dabble\SqlException on error.
     */
    public function column($table, $field, $where = null, $options = null)
    {
        $results = $this->fetch($table, $field, $where, $options);
        return array_shift($results);
    }

    /**
     * Alias for Quibble\Dabble\Adapter::column for consistency with PDO.
     */
    public function fetchColumn($table, $field, $where = null, $options = null)
    {
        return $this->column($table, $field, $where, $options);
    }

    /**
     * Retrieve a single row as an object.
     *
     * @param mixed $class Classname, object or null (defaults to StdClass) to
     *                     select into.
     * @param string $table The table(s) to query.
     * @param string $field The field (column) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return mixed An object of the desired class initialized with the row's
     *  values.
     * @throws Quibble\Dabble\SelectException when no row was found.
     * @throws Quibble\Dabble\SqlException on error.
     */

    public function fetchObject(
        $class = null,
        $table,
        $fields,
        $where = null,
        $options = []
    )
    {
        if (is_null($class)) {
            $class = 'StdClass';
        } elseif (is_object($class)) {
            $class = get_class($class);
        }
        if (is_scalar($fields)) {
            $fields = explode(',', $fields);
        }
        $query = new Select(
            $this,
            $table,
            $fields,
            new Where($where),
            new Options($options)
        );
        $stmt = $this->prepare($query->__toString());
        $stmt->execute($query->getBindings());
        $result = $stmt->fetchObject($class);
        if (!$result) {
            throw new SelectException($stmt->queryString);
        }
        return $result;
    }

    /**
     * Retrieve a count from a table.
     *
     * @param string $table The table(s) to query.
     * @param array $where An SQL where-array.
     * @return integer The number of matched rows.
     * @throws Quibble\Dabble\SqlException on error.
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
     */
    public function insert($table, array $fields)
    {
        $query = new Insert($this, $table, $fields);
        return $query->execute();
    }
    
    /**
     * Update one or more rows in the database.
     *
     * @param string $table The table to update.
     * @param array $fields Array Field => value pairs to update.
     * @param array $where Array of where statements to limit updates.
     * @return integer The number of affected (updated) rows.
     * @throws Quibble\Dabble\UpdateException if no rows were updated.
     * @throws Quibble\Dabble\SqlException on error.
     */
    public function update($table, array $fields, $where, $options = null)
    {
        $query = new Update(
            $this,
            $table,
            $fields,
            new Where($where),
            new Options($options)
        );
        return $query->execute();
    }

    /**
     * Delete a row from the database.
     *
     * @param string $table The table to delete from.
     * @param array $where Array of where statements to limit deletes.
     * @return int The number of deleted rows.
     * @throws Quibble\Dabble\DeleteException if no rows were deleted.
     * @throws Quibble\Dabble\SqlException on error.
     */
    public function delete($table, array $where)
    {
        $query = new Delete($this, $table, new Where($where));
        return $query->execute();
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

    public function now()
    {
        return new Raw('NOW()');
    }

    public function datenull()
    {
        return null;
    }

    public function values($array)
    {
        return array_map($array, [$this, 'value']);
    }

    public function value($val)
    {
        $old = $val;
        $val = $this->_value($val);
        return $val == '?' ? $old : $val;
    }
}

