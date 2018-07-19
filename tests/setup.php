<?php

namespace Quibble\Tests;

use Quibble\Dabble;

global $adapter;

$adapter = new class('sqlite::memory:') extends Dabble\Adapter {
    public function now() : string
    {
        return 'CURRENT_TIMESTAMP';
    }

    public function random() : string
    {
        return 'RANDOM()';
    }

    public function interval($unit, $amount) : string
    {
        return "datetime('now', '$unit {$amount}s";
    }

};

