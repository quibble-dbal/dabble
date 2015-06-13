<?php

require 'SelectTest.php';
require 'InsertTest.php';
require 'UpdateTest.php';
require 'DeleteTest.php';

abstract class AbstractTest extends PHPUnit_Extensions_Database_TestCase
{
    use SelectTest;
    use InsertTest;
    use UpdateTest;
    use DeleteTest;

    protected function getSetUpOperation()
    {
        // Needed for truncates to cascade...
        return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
    }
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__).'/_files/data.xml');
    }
}

