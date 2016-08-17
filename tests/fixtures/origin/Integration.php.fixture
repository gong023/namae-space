<?php

use A\B\Origin;

class Sample extends Origin
{
    public function __construct(Origin $class)
    {
        try {
            if ($class instanceof Origin) {
                Origin\func('arg');
            }
        } catch (Origin $e) {
            throw new Origin();
        }
    }
}

class Sample2 implements Origin
{
}

interface Sample3 extends Origin
{
}
