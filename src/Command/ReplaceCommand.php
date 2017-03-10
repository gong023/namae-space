<?php

namespace NamaeSpace\Command;

use NamaeSpace\ChildProcess\Replace\DryRun;
use NamaeSpace\ChildProcess\Replace\Overwrite;
use NamaeSpace\Command\Context\InvalidReplaceDirException;
use NamaeSpace\Command\Context\ReplaceContext;
use NamaeSpace\ComposerContent;
use NamaeSpace\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_REQUIRED, 'path for composer.json')
            ->addOption('origin_namespace', 'O', InputOption::VALUE_REQUIRED)
            ->addOption('new_namespace', 'N', InputOption::VALUE_REQUIRED)
            ->addOption('replace_dir', 'R', InputOption::VALUE_REQUIRED, 'relative path from project base to put new namespace file. pass this argument if you don\'t wanna be asked')
            ->addOption('max_process', 'M', InputOption::VALUE_REQUIRED, 'max num of process', 10)
            ->addOption('additional_paths', 'A', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'additional paths to search. must be relative from project base path')
            ->addOption('exclude_paths', 'E', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'exclude paths to search.')
            ->addOption('dry_run', 'D', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectRoot = ComposerContent::getRealDir($input->getOption('composer_json'));
        $raw = json_decode(file_get_contents($projectRoot . '/composer.json'), true);
        $composerContent = ComposerContent::instantiate($raw);

        $context = (new ReplaceContext($projectRoot, $input, $composerContent))
            ->setOriginNameFromInput()
            ->setNewNameFromInput();

        try {
            $context->setReplaceDirFromInput();
        } catch (InvalidReplaceDirException $e) {
            $context->replaceDirFallback($this->getHelper('question'), $output);
        }

        $childName = $context->isDryRun() ? DryRun::class : Overwrite::class;

        $this->executeChild($childName, $context, $output);
    }
}
