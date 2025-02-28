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
    private $initializeed = false;

    /**
     * @param string $dsn
     * @param string $username = null
     * @param string $password = null
     * @param array $options
     * @return void
     */
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
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
     * Opens a just-in-time database initializeion associated with this adapter.
     * This allows you to define as many databases as you want in a central file
     * without necessarily worrying about overhead (e.g. lots of related sites).
     *
     * @return void
     * @throws Quibble\Dabble\ConnectionFailedException if the database is
     *  unavailable.
     */
    public function initialize() : void
    {
        if ($this->initializeed) {
            return;
        }
        try {
            extract($this->connectionSettings);
            parent::__construct($dsn, $username, $password, $options);
            $this->initializeed = true;
        } catch (PDOException $e) {
            throw new ConnectionFailedException($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function reinitialize() : void
    {
        $this->initializeed = false;
        $this->initialize();
    }

    /**
     * @return bool
     */
    public function beginTransaction() : bool
    {
        $this->initialize();
        return parent::beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit() : bool
    {
        $this->initialize();
        return parent::commit();
    }

    /**
     * @return string
     */
    public function errorCode() : ?string
    {
        $this->initialize();
        return parent::errorCode();
    }

    /**
     * @return array
     */
    public function errorInfo() : array
    {
        $this->initialize();
        return parent::errorInfo();
    }

    /**
     * @return int
     */
    public function exec(string $statement) : int|false
    {
        $this->initialize();
        return parent::exec($statement);
    }

    /**
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute(int $attribute) : mixed
    {
        $this->initialize();
        return parent::getAttribute($attribute);
    }

    /**
     * @return bool
     */
    public function inTransaction() : bool
    {
        return (bool)$this->transactionLevel;
    }

    /**
     * @param string|null $name
     * @return string|false
     */
    public function lastInsertId(?string $name = null) : string|false
    {
        $this->initialize();
        return parent::lastInsertId($name);
    }

    /**
     * @param string $statement
     * @param array $driver_options
     * @return PDOStatement|false
     */
    public function prepare(string $statement, array $driver_options = []) : PDOStatement|false
    {
        $this->initialize();
        return parent::prepare($statement, $driver_options);
    }

    /**
     * This method has different signatures depending on the `$fetchMode`, so
     * we just use a spread here.
     *
     * @return PDOStatement|false
     */
    public function query(...$args) : PDOStatement|false
    {
        $this->initialize();
        return parent::query(...$args);
    }

    /**
     * @param mixed $string
     * @param int $parameter_type
     * @return string
     */
    public function quote(mixed $string, $parameter_type = PDO::PARAM_STR) : string|false
    {
        $this->initialize();
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
        $this->initialize();
        return parent::rollback();
    }

    /**
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute(int $attribute, mixed $value) : bool
    {
        $this->initialize();
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

