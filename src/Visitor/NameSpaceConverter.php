<?php

namespace NamaeSpace\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;

class NameSpaceConverter extends NodeVisitorAbstract
{
    private $beforeNameSpace;
    private $afterNameSpace;

    public function __construct(Name $beforeNameSpace, Name $afterNameSpace)
    {
        $this->beforeNameSpace = $beforeNameSpace;
        $this->afterNameSpace = $afterNameSpace;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Name) {
            if ($node->toString() === $this->beforeNameSpace->toString()) {
                $nameString = $this->afterNameSpace->getLast();
            } else {
                $nameString = $node->isUnqualified() ? $node->toString() : $node->getLast();
            }

            return new Name($nameString);
        }

        return null;
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $node) {
            /** @var Stmt\Namespace_ $node */
            if ($node instanceof Stmt\Namespace_) {
                $use = new Stmt\Use_([new Stmt\UseUse($this->afterNameSpace)]);

                $node->stmts = array_merge([$use], $node->stmts);
//                print_r($node->getSubNodeNames());
            }
        }

        /** @var Node $node */
        foreach ($nodes as $node) {
            if ($node->getLine() === 1) {
            }
        }
    }
}
