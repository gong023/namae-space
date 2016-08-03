<?php

namespace NamaeSpace\Visitor;

use NamaeSpace\MutableString;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;

class ReplaceVisitor extends NodeVisitorAbstract
{
    public static $targetClass = false;

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
        if ($node instanceof Stmt\ClassLike
            && $node->name === $this->targetName->getLast()
        ) {

            $this->code->addModification(
                $node->getAttribute('startFilePos'),
                'class ' . $node->name,
                'class ' . $this->newName->getLast()
            );
            static::$targetClass = true;

            // do not return to keep modifiy
        }

        if ($node instanceof Expr\New_ && $node->class instanceof Name) {
            if ($node->class->isFullyQualified()
                && $node->class->toString() === $this->targetName->toString()
            ) {
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    'new ' . $node->class->toString(),
                    'new ' . $this->newName->toString()
                );
            } elseif ($node->class->getLast() === $this->targetName->getLast()) {
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    'new ' . $node->class->getLast(),
                    'new ' . $this->newName->getLast()
                );
            }
        }

        return null;
    }
}
