<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\UpdateException;

trait UpdateTest
{
    /**
     * @Description {0}::update should update a row
     */
    public function testUpdate(Adapter $db, $table = 'test', $values = ['name' => 'douglas'], $where = ['id' => 1])
    {
        return 1;
    }

    /**
     * @Description {0}::update should throw an exception if nothing was updated
     */
    public function testNoUpdate(Adapter $db, $table = 'test', $values = ['name' => 'adams'], $where = ['id' => 12345])
    {
        throw new UpdateException;
    }
}

