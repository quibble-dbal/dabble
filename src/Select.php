<?php

/**
 * Class for generating SELECT queries.
 *
 * @package Quibble\Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015, 2016
 */

namespace Quibble\Dabble;

use PDO;
use PDOException;
use PDOStatement;
use ArrayObject;

class Select extends Query
{
    use Normalize;

    protected $fields;
    protected $where;
    protected $options;

    public function __construct(
        Adapter $adapter,
        $table,
        array $fields,
        Where $where,
        Options $options
    ) {
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

    /**
     * Execute the select statement and return a Generator of the result.
     *
     * @return Generator
     */
    public function execute()
    {
        $stmt = parent::execute();
        while (false !== $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->normalize($row);
            yield $row;
        }
    }

    public function __toString()
    {
        $fields = [];
        foreach ($this->fields as $key => $value) {
            if (is_numeric($key)) {
                $fields[] = $value;
            } else {
                $fields[] = "$value $key";
            }
        }
        return sprintf(
            "SELECT %s FROM %s WHERE %s %s",
            implode(', ', $fields),
            $this->table,
            $this->where,
            $this->options
        );
    }
}

