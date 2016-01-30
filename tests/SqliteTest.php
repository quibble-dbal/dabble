<?php

namespace Dabble\Test;

use Dabble\Adapter\Sqlite;

/**
 * Tests for SQLite
 */
class SqliteTest
{
    use SelectTest;
    use InsertTest;
    use UpdateTest;
    use DeleteTest;

    public function __wakeup()
    {
        $this->db = new Sqlite(':memory:');
        $this->db->exec("CREATE TABLE test (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(32),
            status INTEGER NOT NULL DEFAULT 0,
            datecreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        $this->db->exec("CREATE UNIQUE INDEX test_name_key ON test(name)");
        $this->db->exec("CREATE INDEX test_status_key ON test(status)");
        $this->db->exec("CREATE INDEX test_datecreated_key ON test(datecreated)");
        $this->db->exec("CREATE TABLE test2 (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            test INTEGER NOT NULL REFERENCES test(id) ON DELETE CASCADE,
            data VARCHAR(255)
        )");
        $this->db->exec("CREATE INDEX test2_test_key ON test2(test)");
        $this->db->exec("INSERT INTO test VALUES
            (1, 'foo', 15, '2015-03-20 10:00:00'),
            (2, 'bar', 11, '1978-07-13 12:42:42'),
            (3, null, 0, '2000-01-01 00:00:00')");
        $this->db->exec("INSERT INTO test2 VALUES (1, 1, 'lorem ipsum')");
    }
}

