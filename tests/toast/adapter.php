<?php

require __DIR__.'/../setup.php';

/** Tests for adapter */
return function () use ($adapter) : Generator {
    /**When creating a new adapter, it is an instanceof PDO */
    yield function () use ($adapter) {
        global $adapter;
        assert($adapter instanceof PDO);
    };
};

