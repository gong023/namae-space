<?php

namespace NamaeSpace\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class NameSpaceFinder extends NodeVisitorAbstract
{
    /**
     * @var Name
     */
    private $findNameSpace;

    public function __construct(Name $findNameSpace)
    {
        $this->findNameSpace = $findNameSpace;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Name) {
            if ($node->toString() === $this->findNameSpace->toString()) {
                var_dump($node);
            }

            $nameString = $node->isUnqualified() ? $node->toString() : $node->getLast();
            return new Name($nameString);
        }

        return null;
    }

    public function afterTraverse(array $node)
    {
    }
}
