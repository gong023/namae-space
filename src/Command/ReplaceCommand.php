<?php

namespace NamaeSpace\Command;

use NamaeSpace\Command\Argument\ReplaceArgument;
use NamaeSpace\NodeBuilder\ReplaceNodeBuilder;
use NamaeSpace\Stream\FileStream;
use NamaeSpace\Stream\StdStream;
use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Node\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceCommand extends Command
{
    /**
     * @var ReplaceArgument
     */
    private $argument = null;

    /**
     * @var ReplaceNodeBuilder
     */
    private $nodeBuilder;
    /**
     * @var FileStream
     */
    private $fileStream;
    /**
     * @var StdStream
     */
    private $stdStream;

    public function __construct(
        ReplaceNodeBuilder $nodeBuilder,
        FileStream $fileStream,
        StdStream $stdStream
    ) {
        parent::__construct();
        $this->nodeBuilder = $nodeBuilder;
        $this->fileStream = $fileStream;
        $this->stdStream = $stdStream;
    }

    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('interaction', 'I', InputOption::VALUE_NONE)
            ->addOption('find_path', 'F', InputOption::VALUE_OPTIONAL)
            ->addOption('exclude_path', 'E', InputOption::VALUE_OPTIONAL)
            ->addOption('autoload_base_path', 'P', InputOption::VALUE_OPTIONAL)
            ->addOption('before_name_space', 'B', InputOption::VALUE_OPTIONAL)
            ->addOption('after_name_space', 'A', InputOption::VALUE_OPTIONAL);
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
        $this->nodeBuilder->addVisitor(new ReplaceVisitor($beforeNameSpace, $afterNameSpace));

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

            $this->proc($findPath);
        }
    }

    private function proc($filePath)
    {
        $rawCode = $this->fileStream->get($filePath);
        $node = $this->nodeBuilder->traverse($rawCode);
//        if ($this->argument->isDryRun()) {
        if (true) {
            $replacedCode = $this->fileStream->getPrettyPrinter()->prettyPrintFile($node);
            $this->stdStream->putDiff($rawCode, $replacedCode);
        } else {
            // TODO
        }
    }
}
