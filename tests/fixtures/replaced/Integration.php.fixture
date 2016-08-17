<?php

use A\B\Replaced;

class Sample extends Replaced
{
    public function __construct(Replaced $class)
    {
        try {
            if ($class instanceof Replaced) {
                Replaced\func('arg');
            }
        } catch (Replaced $e) {
            throw new Replaced();
        }
    }
}

class Sample2 implements Replaced
{
}

interface Sample3 extends Replaced
{
}
