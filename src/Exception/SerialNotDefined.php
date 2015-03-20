<?php

/**
 * @package monolyth
 * @subpackage adapter
 * @subpackage sql
 */

namespace monolyth\adapter\sql;

class SerialNotDefined_Exception extends Exception
{
    public function __construct($class)
    {
        $this->message = "No serials are defined in model $class.";
    }
}

