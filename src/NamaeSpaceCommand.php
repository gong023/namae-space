<?php

namespace NamaeSpace;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NamaeSpaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('namaespace')
            ->setDescription('interactive command to change namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('hello');
    }
}
