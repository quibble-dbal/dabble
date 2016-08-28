<?php

namespace Quibble\Tests;

use Quibble\Dabble\UpdateException;

/**
 * Updating
 */
class UpdateTest extends Database
{
    /**
     * update should update a row {?} and the new row should contain the new
     * value {?}
     */
    public function testUpdate()
    {
        $result = $this->adapter->update('test', ['foo' => 'douglas'], ['id' => 1]);
        yield assert($result == 1);
        $check = $this->adapter->fetchColumn('test', 'foo', ['id' => 1]);
        yield assert($check == 'douglas');
    }

    /**
     * update should throw an exception if nothing was updated
     */
    public function testNoUpdate(Adapter &$db = null, $table = 'test', $values = ['name' => 'adams'], $where = ['id' => 12345])
    {
        $e = null;
        try {
            $this->adapter->update('test', ['foo' => 'adams'], ['id' => 12345]);
        } catch (UpdateException $e) {
        }
        yield assert($e instanceof UpdateException);
    }
}

