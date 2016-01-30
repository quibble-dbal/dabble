<?php

namespace Dabble\Test;

use Dabble\Adapter;
use Dabble\Query;
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
        yield new Query\SelectException;
    }

    /**
     * {0}::count should return 3 for the 'test' table.
     */
    public function testCount(Adapter &$db = null, $table = 'test')
    {
        $db = $this->db;
        yield 3;
    }

    /**
     * {0}::fetchAll should return 3 rows for the 'test' table.
     */
    public function testAll(Adapter &$db = null, $table = 'test', $fields = '*')
    {
        $db = $this->db;
        yield 'count' => 3;
    }

    /**
     * {0}::fetch should correctly alias a column.
     */
    public function testAlias(Adapter &$db = null, $table = 'test', $fields = ['foo' => 'name'], $where = ['id' => 1])
    {
        $db = $this->db;
        yield ['foo' => 'foo'];
    }

    /**
     * {0}::fetch should be able to handle a subquery.
     */
    public function testSubquery(Adapter &$db = null, $table = 'test', $fields = 'name', &$where = [])
    {
        $db = $this->db;
        $where = ['id' => new Query\Select(
            $db,
            'test2',
            ['test'],
            new Query\Where(['data' => 'lorem ipsum']),
            new Query\Options
        )];
        yield ['name' => 'foo'];
    }
}

