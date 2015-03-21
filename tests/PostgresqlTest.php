<?php

use Dabble\Adapter\Postgresql;

class PostgresqlTest extends AbstractTest
{
    public function getConnection()
    {
        static $db;
        if (!isset($db)) {
            $db = $this->createDefaultDBConnection(
                new Postgresql(
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

