<?php

namespace Quibble\Tests;

use Quibble\Dabble;

global $adapter;

$adapter = new class('sqlite::memory:') extends Dabble\Adapter {
    public function now() : Dabble\Raw
    {
        return new Dabble\Raw('CURRENT_TIMESTAMP');
    }

    public function random() : Dabble\Raw
    {
        return new Dabble\Raw('RANDOM()');
    }

    public function interval($offset, $unit, $amount) : Dabble\Raw
    {
        if ($offset instanceof Dabble\Now) {
            $offset = "'now'";
        }
        return new Dabble\Raw("datetime($offset, '$unit {$amount}s");
    }

};

