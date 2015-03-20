<?php

namespace monolyth\adapter\sql;

class TableDoesntExist_Exception extends Exception
{
    public function __construct($table, $code = 0, \Exception $previous = null)
    {
        parent::__construct($table, $code, $previous);
    }
}

