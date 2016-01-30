<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query\SelectException;
use Carbon\Carbon;

/**
 * Selecting
 */
trait SelectTest
{
    /**
     * {0}::select should yield 3 rows when called with no where
     */
    public function testSelects(Adapter &$db = null, $table = 'test', $fields = '*', $where = [], $options = ['order' => 'id'])
    {
        $db = $this->db;
        yield function ($result) {
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
     * {0}::fetch should yield just the first row
     */
    public function testFetch(Adapter &$db = null, $table = 'test', $fields = '*', $where = [], $options = ['order' => 'id'])
    {
        $db = $this->db;
        yield [
            'id' => "1",
            'name' => 'foo',
            'status' => "15",
            'datecreated' => new Carbon('2015-03-20 10:00:00'),
        ];
    }

    /**
     * {0}::column should yield just a single column
     */
    public function testColumn(Adapter &$db = null, $table = 'test', $fields = '*', $where = [], $options = ['order' => 'id'])
    {
        $db = $this->db;
        yield 1;
    }

    /**
     * For no results, {0}::select should throw an exception
     */
    public function testNoResults(Adapter &$db = null, $table = 'test', $fields = '*', $where = ['id' => 12345])
    {
        $db = $this->db;
        yield new SelectException;
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

