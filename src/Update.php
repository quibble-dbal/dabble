<?php

/**
 * Class for generating UPDATE queries.
 *
 * @package Quibble\Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015, 2016
 */

namespace Quibble\Dabble;

class Update extends Query
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
        $stmt = parent::execute();
        if ($affectedRows = $stmt->rowCount() and $affectedRows) {
            return $affectedRows;
        }
        $info = $stmt->errorInfo();
        $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $this";
        throw new UpdateException($this->error($msg, $this->bound));
    }

    public function __toString()
    {
        $fields = [];
        foreach ($this->fields as $name => $value) {
            $fields[] = sprintf('%s = %s', $name, $value);
        }
        return sprintf(
            "UPDATE %s SET %s WHERE %s %s",
            $this->table,
            implode(', ', $fields),
            $this->where,
            $this->options
        );
    }
}

