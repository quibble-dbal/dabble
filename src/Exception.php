<?php

/**
 * Base Exception that other Dabble-exceptions should extend.
 *
 * @package Quibble\Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2010, 2015, 2016
 */

namespace Quibble\Dabble;

use PDOException;

abstract class Exception extends PDOException
{
    const PREPARATION = 1;
    const EXECUTION = 2;
    const EMPTYRESULT = 3;
    const NOAFFECTEDROWS = 4;
}

