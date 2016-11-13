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

    public function interval($unit, $amount) : Dabble\Raw
    {
        return new Dabble\Raw("datetime('now', '$unit {$amount}s");
    }

};

