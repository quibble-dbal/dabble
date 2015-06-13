<?php

/**
 * Class for generating UPDATE queries.
 *
 * @package Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Query;

use Dabble\Adapter;
use Dabble\Query;

class Update extends Query
{
    protected $fields;
    protected $where;
    protected $options;

    public function __construct(Adapter $adapter, $table, array $fields, Where $where, Options $options = null)
    {
        parent::__construct($adapter, $table);
        $this->fields = $this->prepareBindings($fields);
        $this->where = $where;
        $this->options = $options;
        $this->bound = array_merge(
            $this->bound,
            $where->getBindings(),
            $options->getBindings()
        );
    }

    public function execute()
    {
        $fields = [];
        foreach ($this->fields as $name => $value) {
            $fields[] = sprintf('%s = %s', $name, $value);
        }
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s %s",
            $this->table,
            implode(', ', $fields),
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
                3,
                $e
            );
        }
        if (!(($affectedRows = $stmt->rowCount()) && $affectedRows)) {
            $info = $stmt->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new UpdateException($this->error($msg, $this->bound), 1);
        }
        return $affectedRows;
    }
}

