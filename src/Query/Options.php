<?php

/**
 * Object for generating query options.
 *
 * @package Dabble
 * @subpackage Query;
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Query;

class Options implements Bindable
{
    use Value;

    protected $bound = [];
    protected $options;

    public function __construct(array $options = null)
    {
        $upper = [];
        if ($options) {
            foreach ($options as $key => $value) {
                $upper[strtoupper($key)] = $value;
            }
        }
        $this->options = $this->prepareBindings($upper);
    }

    public function getBindings()
    {
        return $this->bound;
    }

    public function prepareBindings(array $options)
    {
        if (!$options) {
            return [];
        }
        if (isset($options['HAVING'])) {
            $where = new Where($options['HAVING']);
            $this->bound = array_merge($this->bound, $where->getBindings());
            $options['HAVING'] = $where;
        }
        return $options;
    }

    public function __toString()
    {
        $rendered = [];
        if (isset($this->options['GROUP'])) {
            if (!is_array($this->options['GROUP'])) {
                $this->options['GROUP'] = [$this->options['GROUP']];
            }
            $rendered[] = sprintf(
                "GROUP BY %s",
                implode(', ', $this->options['GROUP'])
            );
        }
        if (isset($this->options['HAVING'])) {
            $rendered[] = sprintf("HAVING %s", $this->options['HAVING']);
        }
        if (isset($this->options['ORDER'])) {
            $tmp = [];
            if (!is_array($this->options['ORDER'])) {
                $rendered[] = "ORDER BY {$this->options['ORDER']}";
            } else {
                foreach ($this->options['ORDER'] as $order) {
                    if (!is_array($order)) {
                        $tmp[] = $order;
                        continue;
                    }
                    $dir = array_keys($order);
                    $dir = array_shift($dir);
                    $col = array_shift($order);
                    if (!is_array($col)) {
                        $col = [$col];
                    }
                    foreach ($col as $onecol) {
                        $tmp[] = sprintf(
                            '%s %s',
                            $onecol,
                            strtoupper($dir)
                        );
                    }
                }
                $rendered[] = sprintf(
                    "ORDER BY %s",
                    implode(', ', $tmp)
                );
            }
        }
        if (isset($this->options['LIMIT'])) {
            $rendered[] = sprintf(
                "LIMIT %d",
                $this->options['LIMIT']
            );
        }
        if (isset($this->options['OFFSET'])) {
            $rendered[] = sprintf(
                "OFFSET %d",
                $this->options['OFFSET']
            );
        }
        return implode(' ', $rendered);
    }
}

