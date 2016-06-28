<?php

namespace NamaeSpace\Command;

use NamaeSpace\Command\Argument\FindArgument;
use NamaeSpace\Visitor\FindVisitor;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * less function call and instantiate
 */
class FindCommand extends Command
{
    /**
     * @var FindArgument
     */
    private $argument;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    public function __construct(
        Parser $parser,
        NodeTraverser $traverser
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('find')
            ->setDescription('find namespace in path')
            ->addOption('interaction', 'I', InputOption::VALUE_NONE)
            ->addOption('find_path', 'F', InputOption::VALUE_OPTIONAL)
            ->addOption('exclude_path', 'E', InputOption::VALUE_OPTIONAL)
            ->addOption('find_name_space', 'N', InputOption::VALUE_OPTIONAL);
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
        $findNameSpace = new Name($this->argument->getFindNameSpace());
        $this->traverser->addVisitor(new FindVisitor($findNameSpace, $output));
        $findPath = $this->argument->getFindPath();
        if (strpos($findPath, '.php') !== false) {
            $this->proc($findPath);
            return;
        }

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->fileStream->findFiles($findPath) as $file) {
            $absoluteFilePathName = $file->getRealPath();
            if (preg_match("/{$this->argument->getExcludePath()}/", $absoluteFilePathName)) {
                continue;
            }

            $this->proc($absoluteFilePathName);
        }
    }

    private function proc($filePath)
    {
        $stmts = $this->parser->parse($this->fileStream->get($filePath));
        FindVisitor::$filePath = $filePath;
        $this->traverser->traverse($stmts);
    }
}
