<?php

/**
 * Database abstraction layer for SqLite.
 *
 * @package Dabble
 * @subpackage Adapter
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Adapter;

use Dabble\Adapter as DabbleAdapter;

/** SqLite-abstraction class. */
class Sqlite extends DabbleAdapter
{
    public function __construct($d, $n = null, $p = null, array $o = [])
    {
        return parent::__construct("sqlite:$d", $n, $p, $o);
    }

    public function value($value, &$bind)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return parent::value($value, $bind);
    }

    public function interval($quantity, $amount)
    {
        //and o_orderdate < date('1995-03-01', '+3 month')
        $what = null;
        switch ($quantity) {
            case self::SECOND: $what = 'second'; break;
            case self::MINUTE: $what = 'minute'; break;
            case self::HOUR: $what = 'hour'; break;
            case self::DAY: $what = 'day'; break;
            case self::WEEK: $what = 'week'; break;
            case self::MONTH: $what = 'month'; break;
            case self::YEAR: $what = 'year'; break;
        }
        return sprintf("interval %d %s", $amount, $what);
    }

    public function random()
    {
        return 'RANDOM()';
    }
}

