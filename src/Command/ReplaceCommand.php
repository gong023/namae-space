<?php

namespace NamaeSpace\Command;

use NamaeSpace\ComposerContent;
use NamaeSpace\MutableString;
use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ReplaceCommand extends Command
{
    /**
     * @var ComposerContent
     */
    private $composerContent;

    /**
     * @var Name
     */
    private $targetNameSpace;

    /**
     * @var Name
     */
    private $newNameSpace;

    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_OPTIONAL)
            ->addOption('additional_path', 'A', InputOption::VALUE_OPTIONAL)
            ->addOption('target_namespace', 'T', InputOption::VALUE_OPTIONAL)
            ->addOption('new_namespace', 'N', InputOption::VALUE_OPTIONAL)
            ->addOption('replace_dir', 'R', InputOption::VALUE_OPTIONAL)
            ->addOption('dry_run', 'D', InputOption::VALUE_NONE);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if (! $input->getOption('composer_json')) {
            if (file_exists(__DIR__ . '/../composer.json')) {
                $input->setOption('composer_json', __DIR__ . '/../composer.json');
            } else {
                $question = (new Question('composer.json path: '))
                    ->setValidator(['\\NamaeSpace\\ComposerContent', 'validateExists']);
                $input->setOption('composer_json', $helper->ask($input, $output, $question));
            }
        }

        if (! $input->getOption('target_namespace')) {
            $targetNameSpace = $helper->ask($input, $output, new Question('target name space: '));
            $input->setOption('target_namespace', $targetNameSpace);
        }
        $this->targetNameSpace = new Name($input->getOption('target_namespace'));

        if (! $input->getOption('new_namespace')) {
            $newNameSpace = $helper->ask($input, $output, new Question('new name space: '));
            $input->setOption('new_namespace', $newNameSpace);
        }
        $this->newNameSpace = new Name($input->getOption('new_namespace'));

        $this->composerContent = ComposerContent::instantiate($input->getOption('composer_json'));

        if (! $input->getOption('replace_dir')) {
            $replaceDirs = $this->composerContent->getDirsToReplace($this->newNameSpace);
            $dirsCount = count($replaceDirs);
            if ($dirsCount === 0) {
                throw new \RuntimeException('base dir is not found to put ' . $this->newNameSpace->getLast() . '.php');
            } elseif ($dirsCount === 1) {
                $input->setOption('replace_dir', $replaceDirs[0]);
            } else {
                $question = new ChoiceQuestion(
                    'which dir do you use to put ' . $this->newNameSpace->getLast() . '.php',
                    $replaceDirs
                );
                $input->setOption('replace_dir', $helper->ask($input, $output, $question));
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lexer = new Lexer(['usedAttributes' => ['startFilePos']]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5, $lexer);
        $traverser = new NodeTraverSer();
        $traverser->addVisitor(new NameResolver());
        $visitor = new ReplaceVisitor($this->targetNameSpace, $this->newNameSpace);
        $traverser->addVisitor($visitor);
        $differ = new Differ("--- Original\n+++ New\n", false);

        $search = array_merge(
            $this->composerContent->getFileAndDirsToSearch(),
            (array)$input->getOption('additional_path')
        );

        \NamaeSpace\applyToEachFile(
            $this->composerContent->getReadDirPath(),
            $search,
            function ($basePath, \SplFileInfo $fileInfo) use ($visitor, $traverser, $parser, $differ, $input, $output) {
                $rawCode = file_get_contents($fileInfo->getRealPath());
                $code = new MutableString($rawCode);
                $visitor->setCode($code);
                $stmts = $parser->parse($rawCode);
                $traverser->traverse($stmts);
                $replacedCode = $code->getModified();

                if ($input->getOption('dry_run')) {
                    if ($code->getOrigin() !== $replacedCode) {
                        $output->writeln('<info>' . $fileInfo->getFilename() . '</info>');
                        $output->writeln($differ->diff($code->getOrigin(), $replacedCode));
                    }
                    return;
                } else {
                    if (ReplaceVisitor::$targetClass) {
                        $outputFilePath = "$basePath/{$input->getOption('replace_dir')}/{$visitor->getNewName()->getLast()}.php";
                        @mkdir("$basePath/{$input->getOption('replace_dir')}", 0777, true);
                        file_put_contents($outputFilePath, $code->getModified());
                        @unlink($fileInfo->getRealPath());
                        @rmdir($fileInfo->getPath());
                        ReplaceVisitor::$targetClass = false;
                    } else {
                        file_put_contents($fileInfo->getRealPath(), $replacedCode);
                    }
                }
            }
        );
    }
}
