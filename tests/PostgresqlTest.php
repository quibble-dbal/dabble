<?php

namespace Dabble\Test;

use Dabble\Adapter\Postgresql;

/**
 * Tests for PostgreSQL
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

    public function __wakeup()
    {
        $file = realpath(__DIR__.'/files/postgresql.sql');
        shell_exec("psql -U dabble -d dabble_test < $file 2>/dev/null");
    }
}

