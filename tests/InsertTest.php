<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\InsertException;

/**
 * Insertions
 */
trait InsertTest
{
    /**
     * {0}::insert should insert a new row
     */
    public function testInsert(Adapter &$db = null, $table = 'test', $values = ['name' => 'monomelodies'])
    {
        $db = $this->db;
        yield 1;
    }

    /**
     * {0}::insert should throw an exception if nothing was inserted
     */
    public function testNoInsert(Adapter &$db = null, $table = 'test2', $values = ['test' => null])
    {
        $db = $this->db;
        yield new InsertException;
    }
}

