<?php

namespace NamaeSpace\Command;

use NamaeSpace\ChildProcess\Find;
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
        if (($composerJsonPath = $input->getOption('composer_json')) === null) {
            throw new \RuntimeException('-C:--composer_json is required');
        }
        $projectDir = ComposerContent::getRealDir($composerJsonPath);
        $raw = json_decode(file_get_contents($projectDir . '/composer.json'), true);
        $composerContent = ComposerContent::instantiate($raw);

        if (($findNameOption = $input->getOption('find_namespace')) === null) {
            throw new \RuntimeException('-F:--find_namespace is required');
        }
        $findName = preg_replace('/^\\\/', '', $findNameOption);

        $searchRoots = array_merge(
            $composerContent->getFileAndDirsToSearch(),
            $input->getOption('additional_paths')
        );

        $excludePaths = $input->getOption('exclude_paths');
        $loopOption = ['min_size' => 1, 'max_size' => $input->getOption('max_process')];
        $payload = ['find_name' => $findName];

        // destroy iterator to enqueue smoothly
        $searchPaths = [];
        foreach ($searchRoots as $searchRoot) {
            /** @var \SplFileInfo $fileInfo */
            foreach (\NamaeSpace\getIterator($projectDir . '/' . $searchRoot, $excludePaths) as $fileInfo) {
                $searchPaths[] = $fileInfo->getRealPath();
            }
        }

        $this->executeChild(Find::class, $searchPaths, $loopOption, $payload);
    }
}
