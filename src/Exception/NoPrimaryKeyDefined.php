<?php

/**
 * @package monolyth
 * @subpackage adapter
 * @subpackage sql
 */

namespace monolyth\adapter\sql;

class NoPrimaryKeyDefined_Exception extends Exception
{
    public function __construct($classname)
    {
        $this->message = "The model $classname hasn't any primary keys defined.
            This is a Bad Thing. Either denote a field as
            $classname::TYPE_SERIAL, or manually defined primary keys using the
            'pk' => true key/value pair in your model definition.";
    }
}

