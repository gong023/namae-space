<?php

namespace NamaeSpace\Command;

use NamaeSpace\Command\Argument\FindArgument;
use NamaeSpace\Visitor\NameSpaceConverter;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use PhpParser\ParserFactory;

class ReplaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('interactive command to change namespace')
            ->addOption('interaction', 'I', InputOption::VALUE_NONE)
            ->addOption('find_path', 'F', InputOption::VALUE_NONE)
            ->addOption('exclude_path', 'E', InputOption::VALUE_NONE)
            ->addOption('before_namespace', 'B', InputOption::VALUE_NONE)
            ->addOption('after_namespace', 'A', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = new FindArgument([
            'find_path'          => $input->getOption('find_path'),
            'exclude_path'       => $input->getOption('exclude_path'),
            'autoload_base_path' => $input->getOption('autoload_base_path'),
            'before_name_space'  => $input->getOption('before_namespace'),
            'after_name_space'   => $input->getOption('after_namespace'),
        ]);
        if ($input->getOption('interaction')) {
            $argument
                ->setHelper($this->getHelper('question'), $input, $output)
                ->ask('find_path')
                ->ask('exclude_path')
                ->ask('autoload_base_path')
                ->ask('before_name_space')
                ->ask('after_name_space');
        }

        // test
        $findPath = '/vagrant/ghq/github.com/gong023/Ayaml/src/';
        $beforeNameSpace = 'Ayaml\\Container';
        $afterNameSpace = 'Ayaml\\Container2';

        // boforeFileName, afterFileName

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $traverser = new NodeTraverSer();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new NameSpaceConverter(new Name($beforeNameSpace), new Name($afterNameSpace)));
        $prettyPrinter = new Standard();

        if (strpos('.php', $findPath) !== false) {
            file_get_contents($findPath);
        }

        $finder = new Finder();
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files()->in($findPath) as $file) {
            $absoluteFilePathName = $file->getRealPath();
            if (preg_match('/vendor/', $absoluteFilePathName)) {
                continue;
            }

            $stmts = $parser->parse(file_get_contents($absoluteFilePathName));
            $stmts = $traverser->traverse($stmts);

            $code = $prettyPrinter->prettyPrintFile($stmts);

            echo $code;
        }
    }
}
