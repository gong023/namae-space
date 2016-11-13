<?php

namespace NamaeSpace\Command;

use NamaeSpace\ComposerContent;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ReplaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_OPTIONAL, 'path for composer.json')
            ->addOption('additional_path', 'A', InputOption::VALUE_OPTIONAL, 'additional path to search. must be relative from project base path')
            ->addOption('origin_namespace', 'O', InputOption::VALUE_REQUIRED)
            ->addOption('new_namespace', 'N', InputOption::VALUE_REQUIRED)
            ->addOption('replace_dir', 'R', InputOption::VALUE_OPTIONAL, 'relative path from project base to put new namespace file. pass this argument if you don\'t wanna be asked')
            ->addOption('max_process', 'M', InputOption::VALUE_OPTIONAL, 'max num of process', 30)
            ->addOption('dry_run', 'D', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer_json_dir = ComposerContent::getRealDir($input->getOption('composer_json'));
        $raw = json_decode(file_get_contents($composer_json_dir . '/composer.json'), true);
        $composerContent = ComposerContent::instantiate($raw);

        $originName = preg_replace('/^\\\/', '', $input->getOption('origin_namespace'));
        $originNameSpace = new Name($originName);

        $newName = preg_replace('/^\\\/', '', $input->getOption('new_namespace'));
        $newNameSpace = new Name($newName);

        $replaceDirs = $composerContent->getDirsToReplace($newNameSpace);
        if ($input->getOption('replace_dir')) {
            $replaceDir = $composer_json_dir . '/' . $input->getOption('replace_dir');
            if (! is_dir($replaceDir)) {
                throw new \RuntimeException('invalid replace_dir:' . $replaceDir);
            }
        } else {
            $dirsCount = count($replaceDirs);
            if ($dirsCount === 0) {
                throw new \RuntimeException('base dir is not found to put ' . $newNameSpace->getLast() . '.php');
            } elseif ($dirsCount === 1) {
                $replaceDir = $replaceDirs[0];
            } else {
                /** @var QuestionHelper $helper */
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'which dir do you use to put ' . $newNameSpace->getLast() . '.php',
                    $replaceDirs
                );
                $replaceDir = $helper->ask($input, $output, $question);
            }
        }

//        $replacer = ReplaceProc::create($this->originNameSpace, $this->newNameSpace);

        $search = array_merge(
            $composerContent->getFileAndDirsToSearch(),
            (array)$input->getOption('additional_path')
        );

        \NamaeSpace\applyToEachFile(
            $composer_json_dir,
            $search,
            function ($basePath, \SplFileInfo $fileInfo) use ($replacer, $input, $output) {
//                try {
//                    $code = $replacer->traverse(file_get_contents($fileInfo->getRealPath()));
//                } catch (\PhpParser\Error $e) {
//                    throw new \RuntimeException("<{$fileInfo->getFilename()}> {$e->getMessage()}");
//                }
//
//                if ($input->getOption('dry_run')) {
//                    if ($code->hasModification()) {
//                        $output->writeln('<info>' . $fileInfo->getFilename() . '</info>');
//                        $output->writeln($differ->diff($code->getOrigin(), $code->getModified()));
//                    }
//                    ReplaceVisitor::$targetClass = false;
//                    return;
//                }
//
//                if (ReplaceVisitor::$targetClass) {
//                    ReplaceVisitor::$targetClass = false;
//                    $outputFilePath = "$basePath/{$input->getOption('replace_dir')}/{$this->newNameSpace->getLast()}.php";
//                    @mkdir("$basePath/{$input->getOption('replace_dir')}", 0755, true);
//                    file_put_contents($outputFilePath, $code->getModified());
//                    @unlink($fileInfo->getRealPath());
//                    @rmdir($fileInfo->getPath());
//                } elseif ($code->hasModification()) {
//                    file_put_contents($fileInfo->getRealPath(), $code->getModified());
//                }
            }
        );
    }
}
