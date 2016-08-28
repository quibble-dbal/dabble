<?php

namespace Quibble\Tests;

use Quibble\Dabble;
use Quibble\Dabble\InsertException;

/**
 * Insertions
 */
class InsertTest extends Database
{
    /**
     * insert should insert a new row {?}
     */
    public function testInsert()
    {
        $result = $this->adapter->insert('test', ['foo' => 'monomelodies']);
        yield assert($result == 1);
    }

    /**
     * insert should throw an exception if nothing was inserted {?}
     */
    public function testNoInsert()
    {
        $e = null;
        try {
            $this->adapter->insert('test', ['foo' => null]);
        } catch (InsertException $e) {
        }
        yield assert($e instanceof InsertException);
    }
}

