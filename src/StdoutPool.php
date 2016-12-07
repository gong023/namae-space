<?php

namespace NamaeSpace;

use Symfony\Component\Console\Output\OutputInterface;

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