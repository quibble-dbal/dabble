<?php

namespace Quibble\Tests;

use Quibble\Dabble;

abstract class Database
{
    public function __construct()
    {
        $this->adapter = new class('sqlite::memory:') extends Dabble\Adapter {
        };
    }

    public function __wakeup()
    {
        $this->adapter->exec(<<<EOT
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                foo TEXT NOT NULL
            );
EOT
        );
        $this->adapter->exec(<<<EOT
            INSERT INTO test (foo) VALUES
                ('bar'), ('baz'), ('fizzbuzz');
EOT
        );
    }

    public function __sleep()
    {
        Dabble\Query::clearCache();
        $this->adapter->exec('DROP TABLE test');
    }
}

