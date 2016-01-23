<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\SelectException;

/**
 * @Feature Selecting
 */
trait SelectTest
{
    /**
     * @Scenario {0}::select should return 3 rows when called with no where
     */
    public function testSelects(Adapter &$db = null, $table = 'test', $fields = '*')
    {
        $db = $this->db;
        return function ($result) {
            if (!$result) {
                return false;
            }
            $test = [];
            foreach ($result() as $row) {
                $test[] = (int)$row['id'];
            }
            return $test == [1, 2, 3];
        };
    }

    /**
     * @Scenario {0}::fetch should return just the first row
     */
    public function testFetch(Adapter &$db = null, $table = 'test', $fields = '*', $where = [], $options = ['order' => 'id'])
    {
        $db = $this->db;
        return [
            'id' => 1,
            'name' => 'foo',
            'status' => 15,
            'datecreated' => '2015-03-20 10:00:00',
        ];
    }

    /**
     * @Scenario {0}::column should return just a single column
     */
    public function testColumn(Adapter &$db = null, $table = 'test', $fields = '*', $where = [], $options = ['order' => 'id'])
    {
        $db = $this->db;
        return 1;
    }

    /**
     * @Scenario For no results, {0}::select should throw an exception
     */
    public function testNoResults(Adapter &$db = null, $table = 'test', $fields = '*', $where = ['id' => 12345])
    {
        $db = $this->db;
        throw new SelectException;
    }

    /*
    public function testCount()
    {
        $db = $this->getConnection()->getConnection();
        $cnt = $db->count('test');
        $this->assertEquals(3, (int)$cnt);
    }

    public function testAll()
    {
        $db = $this->getConnection()->getConnection();
        $rows = $db->fetchAll('test', '*');
        $this->assertEquals(3, count($rows));
    }

    public function testAlias()
    {
        $db = $this->getConnection()->getConnection();
        $row = $db->fetch('test', ['foo' => 'name'], ['id' => 1]);
        $this->assertEquals('foo', $row['foo']);
    }

    public function testSubquery()
    {
        $db = $this->getConnection()->getConnection();
        $row = $db->fetch(
            'test',
            '*',
            ['id' => new Dabble\Query\Select(
                $db,
                'test2',
                ['test'],
                new Dabble\Query\Where(['data' => 'lorem ipsum']),
                new Dabble\Query\Options
            )]
        );
        $this->assertEquals('foo', $row['name']);
    }
    */
}

