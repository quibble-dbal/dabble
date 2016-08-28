<?php

namespace Quibble\Tests;

use Quibble\Dabble;
use Carbon\Carbon;

/**
 * Selecting
 */
class SelectTest extends Database
{
    /**
     * select should yield 3 rows when called with no where {?}
     */
    public function testSelects()
    {
        $results = $this->adapter->fetchAll('test', '*');
        yield assert(count($results) == 3);
    }

    /**
     * fetch should yield just the first row {?}
     */
    public function testFetch()
    {
        $result = $this->adapter->fetch('test', 'foo', ['id' => 1]);
        yield assert($result['foo'] == 'bar');
    }

    /**
     * fetchColumn should yield just a single column {?}
     */
    public function testColumn()
    {
        $result = $this->adapter->fetchColumn('test', 'foo', [], ['order' => 'id']);
        yield assert($result == 'bar');
    }

    /**
     * For no results, select should throw an exception {?}
     */
    public function testNoResults()
    {
        $e = null;
        try {
            $this->adapter->select('test', '*', ['id' => 12345]);
        } catch (Dabble\SelectException $e) {
        }
        yield assert($e instanceof Dabble\SelectException);
    }

    /**
     * count should return 3 for the 'test' table {?}
     */
    public function testCount()
    {
        $count = $this->adapter->count('test');
        yield assert($count == 3);
    }

    /**
     * fetch should correctly alias a column {?}
     */
    public function testAlias(Adapter &$db = null, $table = 'test', $fields = ['foo' => 'name'], $where = ['id' => 1])
    {
        $result = $this->adapter->fetch('test', ['name' => 'foo'], ['id' => 1]);
        yield assert(isset($result['name']));
    }

    /**
     * fetch should be able to handle a subquery.
    public function testSubquery(Adapter &$db = null, $table = 'test', $fields = 'name', &$where = [])
    {
        $db = $this->db;
        $where = ['id' => new Dabble\Select(
            $db,
            'test2',
            ['test'],
            new Dabble\Where(['data' => 'lorem ipsum']),
            new Dabble\Options
        )];
        $result = 
        yield ['name' => 'foo'];
    }
     */
}

