<?php

$ret = [$first];
$ret = array_merge($ret, $stmt->fetchAll(PDO::FETCH_ASSOC));
array_walk($ret, [$this, 'normalize']);
return $ret;

