<?php

namespace Quibble\Tests;

use PDO;

require 'setup.php';

/**
 * Test abstract adapter
 */
class Adapter
{
    /**
     * When creating a new adapter, it is an instanceof PDO {?}
     */
    public function adapterInstantiation()
    {
        global $adapter;
        yield assert($adapter instanceof PDO);
    }
}

