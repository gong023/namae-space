<?php

namespace NamaeSpace\NodeBuilder;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;

class ReplaceNodeBuilder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $traverser;

    public function __construct(
        Parser $parser,
        NodeTraverser $traverser
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
    }

    public function addVisitor(NodeVisitor $visitor)
    {
        $this->traverser->addVisitor($visitor);

        return $this;
    }

    /**
     * @param $code
     * @return null|\PhpParser\Node[]
     */
    public function traverse($code)
    {
        $nodes = $this->parser->parse($code);
        $nodes = $this->traverser->traverse($nodes);

        return $nodes;
    }
}
