<?php

namespace Dabble\Test;

trait DeleteTest
{
    public function testDelete()
    {
        $db = $this->getConnection()->getConnection();
        $affectedRows = $db->delete('test', ['id' => 1]);
        $this->assertEquals(1, (int)$affectedRows);
    }
    
    /**
     * @expectedException Dabble\Query\DeleteException
     */
    public function testNoDelete()
    {
        $db = $this->getConnection()->getConnection();
        $db->delete('test', ['id' => 12345]);
    }
}

