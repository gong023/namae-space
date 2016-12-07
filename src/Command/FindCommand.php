<?php

namespace NamaeSpace\Command;

use NamaeSpace\ChildProcess\Find;
use NamaeSpace\ComposerContent;
use NamaeSpace\Command;
use NamaeSpace\StdoutPool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;

class FindCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('find')
            ->setDescription('find namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_REQUIRED, 'path for composer.json')
            ->addOption('find_namespace', 'F', InputOption::VALUE_REQUIRED)
            ->addOption('additional_path', 'A', InputOption::VALUE_REQUIRED, 'additional path to search. must be relative from project base path')
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

        $searchPaths = array_merge(
            $composerContent->getFileAndDirsToSearch(),
            (array)$input->getOption('additional_path')
        );

        $loopOption = ['min_size' => 1, 'max_size' => $input->getOption('max_process')];
        $payload = ['find_name' => $findName];

        foreach ($searchPaths as $searchPath) {
            $loop = EventLoopFactory::create();
            $childProcess = Flexible::createFromClass(Find::class, $loop, $loopOption);
            $targetPath = $projectDir . '/' . $searchPath;
            $this->communicateWithChild($loop, $childProcess, $payload, $targetPath);
        }

        StdoutPool::dump();
    }
}
