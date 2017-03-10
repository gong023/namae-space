<?php

namespace NamaeSpace\Command;

use NamaeSpace\ChildProcess\Find;
use NamaeSpace\Command\Context\FindContext;
use NamaeSpace\ComposerContent;
use NamaeSpace\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FindCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('find')
            ->setDescription('find namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_REQUIRED, 'path for composer.json')
            ->addOption('find_namespace', 'F', InputOption::VALUE_REQUIRED)
            ->addOption('additional_paths', 'A', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'additional paths to search. must be relative from project base path')
            ->addOption('exclude_paths', 'E', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'exclude paths to search.')
            ->addOption('max_process', 'M', InputOption::VALUE_REQUIRED, 'max num of process', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectRoot = ComposerContent::getRealDir($input->getOption('composer_json'));
        $raw = json_decode(file_get_contents($projectRoot . '/composer.json'), true);
        $composerContent = ComposerContent::instantiate($raw);

        $context = (new FindContext($projectRoot, $input, $composerContent))
            ->setFindNameFromInput();

        $this->executeChild(Find::class, $context, $output);
    }
}
