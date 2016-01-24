<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\DeleteException;

/**
 * @Feature Deletion
 */
trait DeleteTest
{
    /**
     * @Scenario {0}::delete should delete a row
     */
    public function testDelete(Adapter &$db = null, $table = 'test', $where = ['id' => 1])
    {
        $db = $this->db;
        return 1;
    }
    
    /**
     * @Scenario {0}::delete should throw an exception if nothing was deleted
     */
    public function testNoDelete(Adapter &$db = null, $table = 'test', $where = ['id' => 12345])
    {
        $db = $this->db;
        throw new DeleteException;
    }
}

