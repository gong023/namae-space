<?php

namespace NamaeSpace\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;

class ReplaceVisitor extends NodeVisitorAbstract
{
    public static $findLines = [];

    private $beforeNameSpace;

    public function __construct(Name $beforeNameSpace)
    {
        $this->beforeNameSpace = $beforeNameSpace;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Name && $node->toString() === $this->beforeNameSpace->toString()) {
            self::$findLines['names'][] = $node->getLine();
        } elseif ($node instanceof Stmt\Namespace_) {
            self::$findLines['namespace'] = $node->getLine();
        } elseif ($node instanceof Stmt\Class_) {
            self::$findLines['classes'][] = $node->getLine();
        } elseif ($node instanceof Stmt\Use_) {
            self::$findLines['uses'][] = $node->getLine();
        }

        return null;
    }
}
