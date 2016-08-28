<?php

namespace Quibble\Dabble;

use Quibble\Dabble\Bindable;
use Quibble\Dabble\Value;
use Quibble\Dabble\SqlException;
use PDOException;

abstract class Query implements Bindable
{
    use Value;

    protected $adapter;
    protected $table;
    protected static $statementCache = [];
    protected $bound = [];

    public function __construct($adapter, $table)
    {
        $this->adapter = $adapter;
        $this->table = $table;
        $id = $adapter->id();
        if (!isset(self::$statementCache[$id])) {
            self::$statementCache[$id] = [];
        }
    }

    public function execute()
    {
        $stmt = $this->statement();
        foreach ($this->bound as &$value) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = "$value";
            }
        }
        try {
            $stmt->execute($this->bound);
        } catch (PDOException $e) {
            throw new SqlException(
                $this->error(
                    "Error in $this: {$e->errorInfo[2]}\n\nParameters:\n",
                    $this->bound
                ),
                SqlException::EXECUTION,
                $e
            );
        }
        return $stmt;
    }

    public function statement()
    {
        try {
            $sql = $this->__toString();
            $id = $this->adapter->id();
            $this->adapter->connect();
            if (!isset(self::$statementCache[$id][$sql])) {
                self::$statementCache[$id][$sql] = $this->adapter->prepare($sql);
            }
            return self::$statementCache[$id][$sql];
        } catch (PDOException $e) {
            throw new SqlException(
                $this->error(
                    "Error in $this: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $this->bound
                ),
                SqlException::PREPARATION,
                $e
            );
        }
    }

    public function getBindings()
    {
        return $this->bound;
    }

    public function prepareBindings(array $data)
    {
        return array_map([$this, 'value'], $data);
    }

    /**
     * Internal helper to correctly format error messages.
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

    public abstract function __toString();
}

