<?php

namespace NamaeSpace\Command;

use NamaeSpace\ChildProcess\Replace\DryRun;
use NamaeSpace\ChildProcess\Replace\Overwrite;
use NamaeSpace\Command\Input\InvalidReplaceDirException;
use NamaeSpace\Command\Input\ReplaceContext;
use NamaeSpace\ComposerContent;
use NamaeSpace\StdoutPool;
use React\EventLoop\Factory as EventLoopFactory;
use NamaeSpace\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;

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
        if (($composerJsonOption = $input->getOption('composer_json')) === null) {
            throw new \RuntimeException('-C:--composer_json is required');
        }
        $projectDir = ComposerContent::getRealDir($composerJsonOption);
        $raw = json_decode(file_get_contents($projectDir . '/composer.json'), true);
        $composerContent = ComposerContent::instantiate($raw);

        if (($originNameOption = $input->getOption('origin_namespace')) === null) {
            throw new \RuntimeException('-O:--origin_namespace is required');
        }
        $originName = preg_replace('/^\\\/', '', $originNameOption);
        if (($newNameSpaceOption = $input->getOption('new_namespace')) === null) {
            throw new \RuntimeException('-N:--new_namespace is required');
        }
        $newName = preg_replace('/^\\\/', '', $newNameSpaceOption);

        if (($replaceDir = $input->getOption('replace_dir')) !== null) {
            if (! is_dir($replaceDir)) {
                throw new \RuntimeException('invalid replace_dir:' . $replaceDir);
            }
        } else {
            $replaceDirs = $composerContent->getDirsToReplace(explode('\\', $newName));
            $dirsCount = count($replaceDirs);
            if ($dirsCount === 0) {
                throw new \RuntimeException('base dir is not found to put ' . $newName . '.php');
            } elseif ($dirsCount === 1) {
                $replaceDir = $replaceDirs[0];
            } else {
                $question = new ChoiceQuestion(
                    'which dir do you use to put ' . $newName . '.php',
                    $replaceDirs
                );
                /** @var QuestionHelper $helper */
                $helper = $this->getHelper('question');
                $replaceDir = $helper->ask($input, $output, $question);
            }
        }

        $excludePaths = $input->getOption('exclude_paths');

        $searchPaths = array_merge(
            $composerContent->getFileAndDirsToSearch(),
            $input->getOption('additional_paths')
        );

        $loopOption = ['min_size' => 1, 'max_size' => $input->getOption('max_process')];
        $payload = [
            'origin_name' => $originName,
            'new_name'    => $newName,
            'project_dir' => $projectDir,
            'replace_dir' => $replaceDir,
        ];

        foreach ($searchPaths as $searchPath) {
            $loop = EventLoopFactory::create();
            if ($input->getOption('dry_run')) {
                $childProcess = Flexible::createFromClass(DryRun::class, $loop, $loopOption);
            } else {
                $childProcess = Flexible::createFromClass(Overwrite::class, $loop, $loopOption);
            }
            $targetPath = $projectDir . '/' . $searchPath;
            $this->communicateWithChild($loop, $childProcess, $payload, $targetPath, $excludePaths);
        }

        StdoutPool::dump();
    }
}
