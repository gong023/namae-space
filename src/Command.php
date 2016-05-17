<?php

namespace NamaeSpace;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command as BaseCommand;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

class Command extends BaseCommand
{

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    /**
     * @var Standard
     */
    protected $prettyPrinter;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(
        Parser $parser,
        NodeTraverser $traverser,
        Standard $prettyPrinter,
        Filesystem $filesystem
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->prettyPrinter = $prettyPrinter;
        $this->filesystem = $filesystem;
        parent::__construct();
    }

}
