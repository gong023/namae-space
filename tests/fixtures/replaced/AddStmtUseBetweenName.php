<?php

namespace X;

use A\B\Replaced;

use Y;

class Sample
{
    public function __construct()
    {
        $this->var = new Replaced;
    }
}
