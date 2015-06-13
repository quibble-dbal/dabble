<?php

/**
 * Class for generating INSERT queries.
 *
 * @package Dabble
 * @subpackage Query;
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Query;

use Dabble\Adapter;
use Dabble\Query;

class Insert extends Query
{
    protected $fields;

    /**
     * Insert a row into the database.
     *
     * @param Dabble\Adapter $adapter The adapter to use.
     * @param string $table The table to insert into.
     * @param array $fields Array of Field => value pairs to insert.
     * @return mixed The last inserted serial, or 0 or true if none found.
     * @throws Dabble\Query\InsertException if no rows were inserted.
     * @throws Dabble\Query\SqlException on error.
     */
    public function __construct(Adapter $adapter, $table, array $fields)
    {
        parent::__construct($adapter, $table);
        $use = [];
        foreach ($fields as $name => $field) {
            if (is_null($field)) {
                continue;
            }
            $use[$name] = $field;
        }
        if (!$use) {
            throw new InsertException(
                "No fields to bind; did you pass only NULL values?"
            );
        }
        $this->fields = $this->prepareBindings($use);
    }

    public function execute()
    {
        try {
            $stmt = parent::execute();
        } catch (PDOException $e) {
            throw new SqlException(
                $this->error(
                    "Error in $this: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $this->bound
                ),
                2,
                $e
            );
        }
        if (!(($affectedRows = $stmt->rowCount()) && $affectedRows)) {
            $info = $stmt->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $this";
            throw new InsertException($this->error($msg, $this->bound));
        }
        return $affectedRows;
    }

    public function __toString()
    {
        return sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            str_replace("'", '', implode(', ', array_keys($this->fields))),
            implode(', ', $this->fields)
        );
    }
}

