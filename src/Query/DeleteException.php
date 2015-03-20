<?php

/**
 * @package Dabble
 * @subpackage Query
 */

namespace Dabble\Query;

class DeleteException extends Exception
{
    public function __construct($sql)
    {
        $this->message = $sql;
    }
}

