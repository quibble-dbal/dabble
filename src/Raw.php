<?php

namespace Quibble\Dabble;

class Raw
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }
}

