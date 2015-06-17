<?php

return function () use ($stmt, &$first) {
    $ret = [$first];
    $ret = array_merge($ret, $stmt->fetchAll(PDO::FETCH_ASSOC));
    return $ret;
};

