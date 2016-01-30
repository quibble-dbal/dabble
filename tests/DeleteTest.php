<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\DeleteException;

/**
 * Deletion
 */
trait DeleteTest
{
    /**
     * {0}::delete should delete a row
     */
    public function testDelete(Adapter &$db = null, $table = 'test', $where = ['id' => 1])
    {
        $db = $this->db;
        yield 1;
    }
    
    /**
     * {0}::delete should throw an exception if nothing was deleted
     */
    public function testNoDelete(Adapter &$db = null, $table = 'test', $where = ['id' => 12345])
    {
        $db = $this->db;
        yield new DeleteException;
    }
}

