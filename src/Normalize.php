<?php

namespace Quibble\Dabble;

use Carbon\Carbon;
use Exception;

trait Normalize
{
    public function normalize(&$row)
    {
        foreach ($row as $key => &$value) {
            if (is_array($value)) {
                array_walk($value, [$this, 'normalize']);
            } elseif (is_numeric($value)) {
                $value = 0 + $value;
            } elseif (preg_match('@^\d{4}-\d{2}-\d{2}@', $value)) {
                try {
                    $value = new Carbon($value);
                } catch (Exception $e) {
                }
            }
        }
    }
}

