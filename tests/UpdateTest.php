<?php

namespace Dabble\Test;

trait UpdateTest
{
    public function testUpdate()
    {
        $db = $this->getConnection()->getConnection();
        $affectedRows = $db->update('test', ['name' => 'douglas'], ['id' => 1]);
        $this->assertEquals(1, (int)$affectedRows);
    }

    /**
     * @expectedException Dabble\Query\UpdateException
     */
    public function testNoUpdate()
    {
        $db = $this->getConnection()->getConnection();
        $db->update('test', ['name' => 'adams'], ['id' => 12345]);
    }
}

