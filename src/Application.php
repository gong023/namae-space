<?php

namespace NamaeSpace;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends BaseApplication
{
    public function getCommandName(InputInterface $input)
    {
        return 'namaespace';
    }
}