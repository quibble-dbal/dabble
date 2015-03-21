<?php

trait InsertTest
{
    public function testInsert()
    {
        $db = $this->getConnection()->getConnection();
        $affectedRows = $db->insert('test', ['name' => 'monomelodies']);
        $this->assertEquals(1, (int)$affectedRows);
    }

    /**
     * @expectedException Dabble\Query\InsertException
     */
    public function testNoInsert()
    {
        $db = $this->getConnection()->getConnection();
        $db->insert('test', ['name' => null]);
    }
}

