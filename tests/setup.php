<?php

use Quibble\Dabble\Adapter;

global $adapter;

$adapter = new class('sqlite:memory') extends Adapter {
};

