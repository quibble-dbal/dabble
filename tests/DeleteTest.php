<?php

namespace Quibble\Tests;

use Quibble\Dabble\DeleteException;

require 'setup.php';

/**
 * Deletion
 */
class DeleteTest extends Database
{
    /**
     * delete should delete a row {?}
     */
    public function testDelete()
    {
        global $adapter;
        $result = $this->adapter->delete('test', ['id' => 1]);
        yield assert($result == 1);
    }
    
    /**
     * delete should throw an exception if nothing was deleted {?}
     */
    public function testNoDelete()
    {
        global $adapter;
        $e = null;
        try {
            $result = $this->adapter->delete('test', ['id' => 12345]);
        } catch (DeleteException $e) {
        }
        yield assert($e instanceof DeleteException);
    }
}

