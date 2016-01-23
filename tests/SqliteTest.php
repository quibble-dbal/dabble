<?php

namespace Dabble\Test;

use Dabble\Adapter\Sqlite;

/**
 * @Description Tests for SQLite
 */
class SqliteTest
{
    use SelectTest;
    use InsertTest;
    use UpdateTest;
    use DeleteTest;

    public function __construct()
    {
        $this->db = new Sqlite(':memory:');
    }
}

