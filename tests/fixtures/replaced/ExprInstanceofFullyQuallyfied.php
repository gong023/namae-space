<?php

use Test\Replaced;

class Sample
{
    public function __construct()
    {
        if ('a' instanceof Replaced) {
            echo 'hi';
        }
    }
}
