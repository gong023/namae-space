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
     * @var ReplaceVisitor
     */
    private $replaceVisitor;

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
        NodeTraverser $traverser,
        ReplaceVisitor $replaceVisitor
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->replaceVisitor = $replaceVisitor;
    }

    public function traverse($rawCode)
    {
        // TODO:fix MutableString not to instantiate here
        $code = new MutableString($rawCode);
        $this->replaceVisitor->setCode($code);
        $stmts = $this->parser->parse($rawCode);
        $this->traverser->traverse($stmts);

        return $code;
    }

    public static function create(Name $originName, Name $newName)
    {
        // TODO: move to the other file except adding ReplaceVisitor
        $lexer = new Lexer(['usedAttributes' => ['startFilePos', 'endFilePos']]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5, $lexer);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $visitor = new ReplaceVisitor($originName, $newName);
        $traverser->addVisitor($visitor);

        return new self($parser, $traverser, $visitor);
    }
}
