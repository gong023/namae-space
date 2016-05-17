<?php

namespace NamaeSpace\Command;

use NamaeSpace\Command\Argument\ReplaceArgument;
use NamaeSpace\Visitor\NameSpaceConverter;
use PhpParser\Node\Name;
use NamaeSpace\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceCommand extends Command
{
    /**
     * @var ReplaceArgument
     */
    private $argument;

    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('interaction', 'I', InputOption::VALUE_NONE)
            ->addOption('find_path', 'F', InputOption::VALUE_NONE)
            ->addOption('exclude_path', 'E', InputOption::VALUE_NONE)
            ->addOption('before_name_space', 'B', InputOption::VALUE_NONE)
            ->addOption('after_name_space', 'A', InputOption::VALUE_NONE);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $excludePath = ($input->getOption('exclude_path')) ? $input->getOption('exclude_path') : 'vendor';
        $this->argument = new ReplaceArgument([
            'find_path'          => $input->getOption('find_path'),
            'exclude_path'       => $excludePath,
            'autoload_base_path' => $input->getOption('autoload_base_path'),
            'before_name_space'  => $input->getOption('before_name_space'),
            'after_name_space'   => $input->getOption('after_name_space'),
        ]);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('interaction')) {
            $this->argument
                ->setHelper($this->getHelper('question'), $input, $output)
                ->ask('find_path')
                ->ask('exclude_path')
                ->ask('autoload_base_path')
                ->ask('before_name_space')
                ->ask('after_name_space');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $afterNameSpace = new Name($this->argument->getAfterNameSpace());
        $beforeNameSpace = new Name($this->argument->getBeforeNameSpace());
        $this->traverser->addVisitor(new NameSpaceConverter($beforeNameSpace, $afterNameSpace));

        $findPath = $this->argument->getFindPath();
        if (strpos($findPath, '.php') !== false) {
            file_get_contents($findPath);
        }

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->finder->files()->in($findPath) as $file) {
            $absoluteFilePathName = $file->getRealPath();
            if (preg_match("/{$this->argument->getExcludePath()}/", $absoluteFilePathName)) {
                continue;
            }

            $stmts = $this->parser->parse(file_get_contents($absoluteFilePathName));
            $stmts = $this->traverser->traverse($stmts);

            $code = $this->prettyPrinter->prettyPrintFile($stmts);

            echo $code;
        }
    }
}
