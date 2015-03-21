<?php

use Dabble\Adapter\Mysql;

class MysqlTest extends AbstractTest
{
    public function getConnection()
    {
        static $db;
        if (!isset($db)) {
            $db = $this->createDefaultDBConnection(
                new Mysql(
                    'dbname=dabble_test;host=localhost',
                    'dabble',
                    'test'
                ),
                'dabble_test'
            );
        }
        return $db;
    }
}

