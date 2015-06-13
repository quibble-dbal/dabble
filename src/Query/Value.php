<?php

namespace Dabble\Query;

trait Value
{
    public function value($value)
    {
        if (is_null($value)) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value instanceof ArrayObject) {
            $value = (array)$value;
        }
        if ($value instanceof Raw) {
            return $value->__toString();
        }
        if (is_object($value)) {
            $value = "$value";
        }
        if (isset($this->bound)) {
            $this->bound[] = $value;
        }
        return '?';
    }
}

