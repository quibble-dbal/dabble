<?php

/**
 * Class for generating DELETE queries.
 *
 * @package Dabble
 * @subpackage Query
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Query;

use Dabble\Query;
use Dabble\Adapter;

class Delete extends Query
{
    protected $where;

    public function __construct(Adapter $adapter, $table, Where $where)
    {
        parent::__construct($adapter, $table);
        $this->where = $where;
        $this->bound = $where->getBindings();
    }

    /**
     * Delete a row from the database.
     *
     * @param string $table The table to delete from.
     * @param array $where Array of where statements to limit deletes.
     * @return int The number of deleted rows.
     * @throws Dabble\Query\DeleteException if no rows were deleted.
     * @throws Dabble\Query\SqlException on error.
     */
    public function execute()
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $this->table,
            $this->where
        );
        try {
            $stmt = parent::execute($sql);
        } catch (PDOException $e) {
            throw new SqlException(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $this->bound
                ),
                4,
                $e
            );
        }
        if (!(($affectedRows = $stmt->rowCount()) && $affectedRows)) {
            $info = $stmt->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new DeleteException($this->error($msg, $this->bound));
        }
        return $affectedRows;
    }
}

