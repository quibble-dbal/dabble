<?php

/**
 * A generic class all database classes involving SQL should extend.
 *
 * @package Quibble\Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2018
 */

namespace Quibble\Dabble;

use PDO;
use PDOException;
use PDOStatement;

abstract class Adapter extends PDO
{
    protected $transactionLevel = 0;
    private $connectionSettings = [];
    private $connected = false;

    /**
     * @param string $dsn
     * @param string $username = null
     * @param string $password = null
     * @param array $options
     * @return void
     */
    public function __construct(string $dsn, string $username = null, string $password = null, array $options = [])
    {
        $this->connectionSettings = compact('dsn', 'username', 'password', 'options');
    }

    /**
     * @return string
     */
    public function id() : string
    {
        return $this->connectionSettings['dsn'].$this->connectionSettings['username'];
    }

    /**
     * Opens a just-in-time database connection associated with this adapter.
     * This allows you to define as many databases as you want in a central file
     * without necessarily worrying about overhead (e.g. lots of related sites).
     *
     * @return void
     * @throws Quibble\Dabble\ConnectionFailedException if the database is
     *  unavailable.
     */
    public function connect() : void
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

    /**
     * @return void
     */
    public function reconnect() : void
    {
        $this->connected = false;
        $this->connect();
    }

    /**
     * @return bool
     */
    public function beginTransaction() : bool
    {
        $this->connect();
        return parent::beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit() : bool
    {
        $this->connect();
        return parent::commit();
    }

    /**
     * @return string
     */
    public function errorCode() : string
    {
        $this->connect();
        return parent::errorCode();
    }

    /**
     * @return array
     */
    public function errorInfo() : array
    {
        $this->connect();
        return parent::errorInfo();
    }

    /**
     * @return int
     */
    public function exec($statement) : int
    {
        $this->connect();
        return parent::exec($statement);
    }

    /**
     * @return int
     */
    public function getAttribute($attribute) : int
    {
        $this->connect();
        return parent::getAttribute($attribute);
    }

    /**
     * @return bool
     */
    public function inTransaction() : bool
    {
        return $this->transactionLevel;
    }

    /**
     * @return string
     */
    public function lastInsertId($name = null) : string
    {
        $this->connect();
        return parent::lastInsertId($name);
    }

    /**
     * @return PDOStatement|null
     */
    public function prepare($statement, $driver_options = null) :? PDOStatement
    {
        $this->connect();
        $driver_options = $driver_options ?: [];
        $stmt = parent::prepare($statement, $driver_options);
        return $stmt ? $stmt : null;
    }

    /**
     * @return PDOStatement
     */
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs) : PDOStatement
    {
        $this->connect();
        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }

    /**
     * @param mixed $string
     * @param int $parameter_type
     * @return string
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR) : string
    {
        $this->connect();
        if (is_object($string) && $string instanceof Raw) {
            return "$string";
        }
        return parent::quote($string, $parameter_type);
    }

    /**
     * @return bool
     */
    public function rollback() : bool
    {
        $this->connect();
        return parent::rollback();
    }

    /**
     * @return bool
     */
    public function setAttribute($attribute, $value) : bool
    {
        $this->connect();
        return parent::setAttribute($attribute, $value);
    }
    /**
     * }}}
     */

    /**
     * @return string
     */
    public abstract function now() : string;

    /**
     * @return string
     */
    public abstract function random() : string;

    /**
     * @param string $unit
     * @param int $amount
     * @return string
     */
    public abstract function interval(string $unit, int $amount) : string;
}

