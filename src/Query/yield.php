<?php

return function () use ($stmt, &$first) {
    if ($first) {
        $yield = $first;
        $first = false;
        $this->normalize($yield);
        yield $yield;
    }
    while (false !== $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $this->normalize($row);
        yield $row;
    }
    return;
};

