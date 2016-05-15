<?php

namespace NamaeSpace;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Finder\Finder;
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
     * @var Finder
     */
    protected $finder;

    /**
     * @var Standard
     */
    protected $prettyPrinter;

    public function __construct(
        Parser $parser,
        NodeTraverser $traverser,
        Standard $prettyPrinter,
        Finder $finder
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->finder = $finder;
        $this->prettyPrinter = $prettyPrinter;
    }

}
