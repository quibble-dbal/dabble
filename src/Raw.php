<?php

namespace Dabble;

class Raw
{
    private $value;

    public __construct($value)
    {
        $this->value = $value;
    }

    public __toString()
    {
        return $this->value;
    }
}

