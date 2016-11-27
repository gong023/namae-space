<?php

namespace NamaeSpace;

final class NullableArray implements \ArrayAccess
{
    public function __construct(array $array = null)
    {
        if ($array !== null) {
            $this->container = $array;
        }
    }

    public function offsetGet($offset)
    {
        if (isset($this->container[$offset])) {
            if (is_array($this->container[$offset])) {
                return new self($this->container[$offset]);
            }
            return $this->container[$offset];
        }

        return new self();
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
}
