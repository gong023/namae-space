<?php

namespace NamaeSpace;

class NullObject
{
    public function __call($name, $arguments)
    {
        return $this;
    }

    public function __get($name)
    {
        return $this;
    }

    public static function __callStatic($name, $arguments)
    {
        return new self;
    }
}
