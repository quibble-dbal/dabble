<?php

/**
 * @package monolyth
 * @subpackage db
 * @subpackage sql
 */

namespace monolyth\adapter\sql;

class DeleteNone_Exception extends Exception
{
    public function __construct($sql)
    {
        $this->message = $sql;
    }
}

