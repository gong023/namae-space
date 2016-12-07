<?php

namespace NamaeSpace;

class StdoutPool
{
    public static $stdouts = [];

    public static function dump()
    {
        \NamaeSpace\write("\n");
        foreach (self::$stdouts as $stdout) {
            if ($stdout !== null) {
                \NamaeSpace\write("$stdout\n");
            }
        }
    }
}