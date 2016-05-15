<?php

namespace NamaeSpace\Command;

use Symfony\Component\Console\Command\Command;

class FindCommand extends Command
{
    public function configure()
    {
        $this->setName('find');
    }
}
