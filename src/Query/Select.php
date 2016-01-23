<?php

/**
 * Class for generating SELECT queries.
 *
 * @package Dabble
 * @subpackage Query;
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015, 2016
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

    public function execute()
    {
        $stmt = parent::execute();
        if (false === ($first = $stmt->fetch(PDO::FETCH_ASSOC))) {
            throw new SelectException($stmt->queryString);
        }
        if (PHP_VERSION_ID < 50500) {
            return include 'simple.php';
        } else {
            return include 'yield.php';
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

