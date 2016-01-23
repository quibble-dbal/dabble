<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\InsertException;

trait InsertTest
{
    /**
     * @Description {0}::insert should insert a new row
     */
    public function testInsert(Adapter $db, $table = 'test', $values = ['name' => 'monomelodies'])
    {
        return 1;
    }

    /**
     * @Description {0}::insert should throw an exception if nothing was inserted
     */
    public function testNoInsert(Adapter $db, $table = 'test', $values = ['name' => null])
    {
        throw new InsertException;
    }
}

