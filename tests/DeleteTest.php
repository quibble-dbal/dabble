<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\DeleteException;

trait DeleteTest
{
    /**
     * @Description {0}::delete should delete a row
     */
    public function testDelete(Adapter $db, $table = 'test', $where = ['id' => 1])
    {
        return 1;
    }
    
    /**
     * @Description {0}::delete should throw an exception if nothing was deleted
     */
    public function testNoDelete(Adapter $db, $table = 'test', $where = ['id' => 12345])
    {
        return new DeleteException;
    }
}

