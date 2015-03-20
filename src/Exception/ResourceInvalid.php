<?php

/**
 * @package monolyth
 * @subpackage adapter
 * @subpackage sql
 */

namespace monolyth\adapter\sql;

class ResourceInvalid_Exception extends Exception
{
    public function __construct($handle)
    {
        $this->message = $handle;
    }
}

?>
