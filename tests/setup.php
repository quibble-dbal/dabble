<?php

namespace Quibble\Tests;

use Quibble\Dabble;

global $adapter;

$adapter = new class('sqlite::memory:') extends Dabble\Adapter {
};

