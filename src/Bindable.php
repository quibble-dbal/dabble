<?php

namespace Quibble\Dabble;

interface Bindable
{
    public function getBindings();
    public function prepareBindings(array $data);
}

