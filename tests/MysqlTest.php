<?php

namespace Dabble\Test;

use Dabble\Adapter\Mysql;

/**
 * @Feature Tests for MySQL
 */
class MysqlTest
{
    use SelectTest;
    use InsertTest;
    use UpdateTest;
    use DeleteTest;

    public function __construct()
    {
        $this->db = new Mysql(
            'dbname=dabble_test;host=localhost',
            'dabble',
            'test'
        );
    }
    
    public function __wakeup()
    {
        $file = realpath(__DIR__.'/files/mysql.sql');
        shell_exec("mysql -u dabble -ptest dabble_test < $file 2>/dev/null");
    }
}

