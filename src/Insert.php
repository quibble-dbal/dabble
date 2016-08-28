<?php

/**
 * Class for generating INSERT queries.
 *
 * @package Quibble\Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015, 2016
 */

namespace Quibble\Dabble;

class Insert extends Query
{
    protected $fields;

    /**
     * Insert a row into the database.
     *
     * @param Quibble\Dabble\Adapter $adapter The adapter to use.
     * @param string $table The table to insert into.
     * @param array $fields Array of Field => value pairs to insert.
     * @return mixed The last inserted serial, or 0 or true if none found.
     * @throws Quibble\Dabble\InsertException if no rows were inserted.
     * @throws Quibble\Dabble\SqlException on error.
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
        $stmt = parent::execute();
        if ($affectedRows = $stmt->rowCount() and $affectedRows) {
            return $affectedRows;
        }
        $info = $stmt->errorInfo();
        $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $this";
        throw new InsertException($this->error($msg, $this->bound));
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

