<?php

namespace NamaeSpace;

use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class ReplaceProc
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $traverser;

    /**
     * @var ReplaceVisitor
     */
    private $visitor;

    public function __construct(
        Parser $parser,
        NodeTraverser $traverser,
        ReplaceVisitor $visitor
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->visitor = $visitor;
    }

    public function replace($rawCode)
    {
        // TODO:fix MutableString not to instantiate here
        $code = new MutableString($rawCode);
        $this->visitor->setCode($code);
        $stmts = $this->parser->parse($rawCode);
        $this->traverser->traverse($stmts);

        return $code;
    }

    public static function create(Name $originName, Name $newName)
    {
        // TODO: move to the other file except adding ReplaceVisitor
        $lexer = new Lexer(['usedAttributes' => ['startFilePos']]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5, $lexer);
        $traverser = new NodeTraverSer();
        $traverser->addVisitor(new NameResolver());
        $visitor = new ReplaceVisitor($originName, $newName);
        $traverser->addVisitor($visitor);

        return new self($parser, $traverser, $visitor);
    }
}
