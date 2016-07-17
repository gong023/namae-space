<?php

namespace NamaeSpace;

class NullableArray
{
    public function __construct(array $origin)
    {
        foreach ($origin as $key => $value) {
            $key = str_replace(['-', '\\'], '_', $key);
            if (is_array($value)) {
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
