<?php

namespace Dabble\Test;

use Dabble\Adapter\Postgresql;

/**
 * @Feature Tests for PostgreSQL
 */
class PostgresqlTest
{
    use SelectTest;
    use InsertTest;
    use UpdateTest;
    use DeleteTest;

    public function __construct()
    {
        $this->db = new Postgresql(
            'dbname=dabble_test;host=localhost',
            'dabble',
            'test'
        );
    }
}

