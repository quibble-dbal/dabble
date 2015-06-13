<?php

/**
 * Class for generating SELECT queries.
 *
 * @package Dabble
 * @subpackage Query;
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Query;

use Dabble\Query;
use Dabble\Adapter;
use PDO;
use PDOException;
use PDOStatement;
use ArrayObject;

class Select extends Query
{
    protected $fields;
    protected $where;
    protected $options;

    public function __construct(Adapter $adapter, $table, array $fields, Where $where, Options $options)
    {
        parent::__construct($adapter, $table);
        $this->fields = $fields;
        $this->bound = array_merge(
            $this->bound,
            $where->getBindings(),
            $options->getBindings()
        );
        $this->where = $where;
        $this->options = $options;
    }

    public function execute()
    {
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s %s",
            implode(', ', $this->fields),
            $this->table,
            $this->where,
            $this->options
        );
        try {
            $stmt = parent::execute($sql);
        } catch (PDOException $e) {
            throw new SqlException(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $this->bound
                ),
                1,
                $e
            );
        }
        return $stmt;
    }
}

