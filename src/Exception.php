<?php

/**
 * Base Exception that other Dabble-exceptions should extend.
 *
 * @package Dabble
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2010, 2015
 */

namespace Dabble;

use PDOException;

/**
 * This is just a stub and shouldn't get thrown.
 */
abstract class Exception extends PDOException
{
}

