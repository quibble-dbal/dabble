<?php

/**
 * @package Dabble
 * @subpackage Query
 */

namespace Dabble\Query;

class SelectException extends Exception
{
    public function __construct($m = '', $c = null, $p = null)
    {
        parent::__construct($m, self::EMPTYRESULT, $p);
    }
}

