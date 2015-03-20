<?php

use Dabble\Adapter\Mysql;

class MysqlTest extends PHPUnit_Extensions_Database_TestCase
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

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__).'/_files/data.xml');
    }

    public function testAllRows()
    {
        $db = $this->getConnection()->getConnection();
        $result = $db->select('test', '*');
        $test = [];
        foreach ($result() as $row) {
            $test[] = (int)$row['id'];
        }
        $this->assertEquals([1, 2, 3], $test);

        // Re-query should also work, yielding a new result set:
        $result = $db->select('test', '*');
        $test = [];
        foreach ($result() as $row) {
            $test[] = (int)$row['id'];
        }
        $this->assertEquals([1, 2, 3], $test);
    }

    public function testOtherSelectMethods()
    {
        $db = $this->getConnection()->getConnection();
        $result = $db->fetch('test', '*');
        $this->assertEquals(1, (int)$result['id']);

        $result = $db->column('test', 'id');
        $this->assertEquals(1, (int)$result);
    }
}

