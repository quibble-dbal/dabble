<?php

use Dabble\Adapter\Mysql;

abstract class AbstractTest extends PHPUnit_Extensions_Database_TestCase
{
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__).'/_files/data.xml');
    }

    public function testSelects()
    {
        $db = $this->getConnection()->getConnection();
        $result = $db->select('test', '*');
        $test = [];
        foreach ($result() as $row) {
            $test[] = (int)$row['id'];
        }
        $this->assertEquals([1, 2, 3], $test);

        // Re-query should also work, yielding a new result set:
        $result = $db->select('test', '*', [], ['order' => 'id']);
        $test = [];
        foreach ($result() as $row) {
            $test[] = (int)$row['id'];
        }
        $this->assertEquals([1, 2, 3], $test);

        $db = $this->getConnection()->getConnection();
        $result = $db->fetch('test', '*', [], ['order' => 'id']);
        $this->assertEquals(1, (int)$result['id']);

        $result = $db->column('test', 'id', [], ['order' => 'id']);
        $this->assertEquals(1, (int)$result);
    }
}

