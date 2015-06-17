<?php

return function () use ($stmt, &$first) {
    if ($first) {
        $yield = $first;
        $first = false;
        yield $yield;
    }
    while (false !== $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
    return;
};

