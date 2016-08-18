<?php

use Test\B\Replaced;

class Sample
{
    public function __construct()
    {
        $this->value = new Replaced('argument');
    }
}
