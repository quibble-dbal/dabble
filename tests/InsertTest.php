<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\InsertException;

/**
 * @Feature Insertions
 */
trait InsertTest
{
    /**
     * @Scenario {0}::insert should insert a new row
     */
    public function testInsert(Adapter &$db = null, $table = 'test', $values = ['name' => 'monomelodies'])
    {
        $db = $this->db;
        return 1;
    }

    /**
     * @Scenario {0}::insert should throw an exception if nothing was inserted
     */
    public function testNoInsert(Adapter &$db = null, $table = 'test', $values = ['name' => null])
    {
        $db = $this->db;
        throw new InsertException;
    }
}

