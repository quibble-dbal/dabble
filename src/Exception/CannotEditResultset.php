<?php

namespace monolyth\adapter\sql;

class CannotEditResultset_Exception extends Exception
{
    public function __construct()
    {
        parent::__construct('unknown', 42);
    }
}

