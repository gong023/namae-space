<?php

namespace NamaeSpace\Command;

use NamaeSpace\Command\Argument\FindArgument;
use NamaeSpace\Visitor\NameSpaceFinder;
use PhpParser\Node\Name;
use NamaeSpace\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FindCommand extends Command
{
    /**
     * @var FindArgument
     */
    private $argument;

    protected function configure()
    {
        $this->setName('find')
            ->setDescription('find namespace in path')
            ->addOption('interaction', 'I', InputOption::VALUE_NONE)
            ->addOption('find_path', 'F', InputOption::VALUE_NONE)
            ->addOption('exclude_path', 'E', InputOption::VALUE_NONE)
            ->addOption('find_name_space', 'N', InputOption::VALUE_NONE);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $excludePath = ($input->getOption('exclude_path')) ? $input->getOption('exclude_path') : 'vendor';
        $this->argument = new FindArgument([
            'find_path'       => $input->getOption('find_path'),
            'find_name_space' => $input->getOption('find_name_space'),
            'exclude_path'    => $excludePath,
        ]);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('interaction')) {
            $this->argument
                ->setHelper($this->getHelper('question'), $input, $output)
                ->ask('find_path')
                ->ask('find_name_space')
                ->ask('exclude_path');
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->traverser->addVisitor(new NameSpaceFinder(new Name($this->argument->getFindNameSpace())));

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->finder->files()->in($this->argument->getFindPath()) as $file) {
            $absoluteFilePathName = $file->getRealPath();
            if (preg_match("/{$this->argument->getExcludePath()}/", $absoluteFilePathName)) {
                continue;
            }

            $stmts = $this->parser->parse(file_get_contents($absoluteFilePathName));
            $this->traverser->traverse($stmts);
        }
    }
}
