<?php

/**
 * @package Dabble
 * @subpackage Query
 */

namespace Dabble\Query;

class InsertException extends Exception
{
    public function __construct($sql)
    {
        $this->message = $sql;
    }
}

