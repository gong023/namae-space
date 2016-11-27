<?php

namespace NamaeSpace\Command;

use NamaeSpace\ChildProcess\Replace\DryRun;
use NamaeSpace\ChildProcess\Replace\Overwrite;
use NamaeSpace\ComposerContent;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\Factory\Fixed;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class ReplaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_REQUIRED, 'path for composer.json')
            ->addOption('additional_path', 'A', InputOption::VALUE_OPTIONAL, 'additional path to search. must be relative from project base path')
            ->addOption('origin_namespace', 'O', InputOption::VALUE_REQUIRED)
            ->addOption('new_namespace', 'N', InputOption::VALUE_REQUIRED)
            ->addOption('replace_dir', 'R', InputOption::VALUE_OPTIONAL, 'relative path from project base to put new namespace file. pass this argument if you don\'t wanna be asked')
            ->addOption('max_process', 'M', InputOption::VALUE_OPTIONAL, 'max num of process', 30)
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

        if ($replaceDir = $input->getOption('replace_dir')) {
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

        $searchPaths = array_merge(
            $composerContent->getFileAndDirsToSearch(),
            (array)$input->getOption('additional_path')
        );

        $loop = EventLoopFactory::create();
        $loopOption = ['min_size' => 1, 'max_size' => $input->getOption('max_process')];
        $payload = [
            'origin_name' => $originName,
            'new_name'    => $newName,
            'project_dir' => $projectDir,
            'replace_dir' => $replaceDir,
        ];
        if ($input->getOption('dry_run')) {
            $childProcess = Fixed::createFromClass(DryRun::class, $loop, $loopOption);
        } else {
            $childProcess = Fixed::createFromClass(Overwrite::class, $loop, $loopOption);
        }

        foreach ($searchPaths as $searchPath) {
            $targetPath = $projectDir . '/' . $searchPath;
            $childProcess->then(function (PoolInterface $pool) use ($payload, $targetPath, $loop, $output) {
                \NamaeSpace\applyToEachFile($targetPath, function (SplFileInfo $fileInfo, $isEnd) use ($pool, $loop, $payload, $output) {
                    $payload['target_real_path'] = $fileInfo->getRealPath();
                    $pool->rpc(MessagesFactory::rpc('return', $payload))
                        ->then(function (Payload $payload) use ($isEnd, $pool, $loop, $output) {
                            $output->write($payload['stdout']);
                            if ($isEnd) {
                                $pool->terminate(MessagesFactory::message());
                                $loop->stop();
                            }
                        }, function (Payload $payload) use ($output) {
                            $output->writeln($payload['exception_class']);
                            $output->writeln($payload['exception_message']);
                        });
                });
            });

            $loop->run();
        }
    }
}
