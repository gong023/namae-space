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
        $options = array_merge($input->getOptions(), ['exclude_path' => $excludePath]);
        $this->argument = new ReplaceArgument($options);
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
        $this->nodeBuilder->addVisitor(new ReplaceVisitor($beforeNameSpace));

        $findPath = $this->argument->getFindPath();
        if (strpos($findPath, '.php') !== false) {
            $this->proc($findPath, $beforeNameSpace, $afterNameSpace);
            return;
        }

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->fileStream->findFiles($findPath) as $file) {
            $realPath = $file->getRealPath();
            if (preg_match("/{$this->argument->getExcludePath()}/", $realPath)) {
                continue;
            }

            $this->proc($realPath, $beforeNameSpace, $afterNameSpace);
        }
    }

    private function proc($filePath, Name $beforeNameSpace, Name $afterNameSpace)
    {
        $rawCode = $this->fileStream->get($filePath);
        $this->nodeBuilder->traverse($rawCode);
        if (ReplaceVisitor::$findLines['names']) {
            $code = [];
            foreach (explode("\n", $rawCode) as $index => $line) {
                if (! in_array($index + 1, ReplaceVisitor::$findLines['names'], true)) {
                    $code[] = $line;
                    continue;
                }
                $code[] = str_replace($beforeNameSpace->getLast(), $afterNameSpace->getLast(), $line);
            }
            $toCode = implode("\n", $code);

            if ($this->argument->isDryRun()) {
                $this->stdStream->putDiff($rawCode, $toCode);
            }
        }
        ReplaceVisitor::$findLines = [];
    }
}
