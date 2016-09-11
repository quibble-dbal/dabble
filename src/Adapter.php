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

    public abstract function now() : Raw;

    public abstract function random() : Raw;

    public abstract function interval($offset, $unit, $amount) : Raw;
}

