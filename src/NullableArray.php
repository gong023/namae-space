<?php

namespace NamaeSpace;

class NullableArray
{
    public function __construct(array $origin)
    {
        foreach ($origin as $key => $value) {
            if (is_array($value)) {
                $key = str_replace('-', '_', $key);
                $this->{$key} = new static($value);
                continue;
            }
            $this->{$key} = $value;
        }
    }

    public function __get($name)
    {
        return new NullObject();
    }
}
