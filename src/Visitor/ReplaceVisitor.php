<?php

namespace NamaeSpace\Visitor;

use NamaeSpace\MutableString;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;

class ReplaceVisitor extends NodeVisitorAbstract
{
    /**
     * @var Name
     */
    private $targetName;

    /**
     * @var Name
     */
    private $newName;

    /**
     * @var MutableString
     */
    private $code;

    public function __construct(Name $targetName, Name $newName)
    {
        $this->targetName = $targetName;
        $this->newName = $newName;
    }

    public function setCode(MutableString $code)
    {
        $this->code = $code;

        return $this;
    }

    public function leaveNode(Node $node)
    {
        // TODO: if node name is not only last
        if ($node instanceof Name && $node->toString() === $this->targetName->getLast()) {
            echo 'a';
            $this->code->addModification(
                $node->getAttribute('startFilePos'),
                $node->toString(),
                $this->newName->getLast()
            );
        } elseif ($node instanceof Stmt\Class_) {
        }

        return null;
    }
}
