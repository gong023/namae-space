<?php

namespace NamaeSpace;

use Symfony\Component\Console\Output\OutputInterface;

class StdoutPool
{
    public static $stdouts = [];

    public static function dump(OutputInterface $output)
    {
        $output->writeln('');
        foreach (self::$stdouts as $stdout) {
            if ($stdout !== null) {
                $output->writeln($stdout);
            }
        }
    }
}