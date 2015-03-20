<?php

/**
 * @package monolyth
 * @subpackage adapter
 * @subpackage sql
 */

namespace monolyth\adapter\sql;

class InsertNone_Exception extends Exception
{
    public function __construct($sql)
    {
        $this->message = $sql;
    }
}

?>
