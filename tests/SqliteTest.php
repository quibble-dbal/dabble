<?php

namespace Dabble\Test;

use Dabble\Adapter\Sqlite;

class SqliteTest extends AbstractTest
{
    public function getConnection()
    {
        static $db;
        if (!isset($db)) {
            $db = $this->createDefaultDBConnection(
                new Sqlite(':memory:'),
                'dabble_test'
            );
            $schema = file_get_contents(
                dirname(__FILE__).'/_files/schema.sqlite.sql'
            );
            $db->getConnection()->exec($schema);
        }
        return $db;
    }
}

