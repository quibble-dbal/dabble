<?php

/**
 * @package Dabble
 */

namespace Dabble;

use monolyth\utils;
use ArrayAccess;
use Iterator;
use Countable;
use PDOStatement;

class Resultset implements ArrayAccess, Iterator, Countable
{
    private $resource = null,
            $elements = 0,
            $total = 0,
            $limit = null,
            $offset = 0,
            $data = [],
            $iterator = null,
            $_count = 0,
            $index = 0;

    public function __construct(Adapter $db, PDOStatement $src,
        $bnd, $lim, $off, $o = null
    )
    {
        $pdo = $db->pdo;
        if (!isset($o)) {
            $this->data = $src->fetchAll($pdo::FETCH_ASSOC);
        } else {
            if (is_string($o)) {
                $o = new $o;
            }
            if ($data = $src->fetchAll()) {
                foreach ($data as $row) {
                    $o = clone $o;
                    $this->data[] = $o->load($row);
                }
            }
        }
        $max = $this->_count = count($this->data);
        if (!$max) {
            throw new NoResults_Exception($src->queryString, $bnd);
        }
        $max = min($max, $lim);
        $this->total = $db->numRowsTotal($src, $bnd);
        $this->limit = $lim;
        $this->offset = $off;
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    public function current()
    {
        return $this->data[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->index++;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        return array_key_exists($this->index, $this->data);
    }

    public function getArrayCopy()
    {
        return $this->data;
    }

    public function count()
    {
        return $this->_count;
    }

    public function countAll()
    {
        return $this->total;
    }

    public function getPageSize()
    {
        if ($this->limit === 0 || !$this->total || !$this->count()) {
            // No results means no pages.
            return 0;
        }
        if (!isset($this->limit)) {
            // No limit means only one page.
            return 1;
        }
        return ceil($this->total / $this->limit);
    }

    public function getFirstPage()
    {
        return $this->getPageSize() ? 1 : null;
    }

    public function getPreviousPage()
    {
        if (!$this->offset || !$this->limit) {
            // No offsets/limits means no previous page.
            return null;
        }
        $current = $this->getCurrentPage();
        return $current ? $current - 1 : null;
    }

    public function getCurrentPage()
    {
        if (!$this->offset || !$this->limit) {
            return $this->getFirstPage();
        }
        return floor($this->offset / $this->limit) + 1;
    }

    public function getNextPage()
    {
        if (!$this->limit) {
            // No limit means no next page.
            return null;
        }
        $current = $this->getCurrentPage();
        return $current < $this->getPageSize() ? $current + 1 : null;
    }

    public function getLastPage()
    {
        return $this->getPageSize();
    }
}

