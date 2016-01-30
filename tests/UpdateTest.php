<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\UpdateException;

/**
 * Updating
 */
trait UpdateTest
{
    /**
     * {0}::update should update a row
     */
    public function testUpdate(Adapter &$db = null, $table = 'test', $values = ['name' => 'douglas'], $where = ['id' => 1])
    {
        $db = $this->db;
        yield 1;
    }

    /**
     * {0}::update should throw an exception if nothing was updated
     */
    public function testNoUpdate(Adapter &$db = null, $table = 'test', $values = ['name' => 'adams'], $where = ['id' => 12345])
    {
        $db = $this->db;
        yield new UpdateException;
    }
}

