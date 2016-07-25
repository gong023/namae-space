<?php

namespace NamaeSpace\Visitor;

use NamaeSpace\MutableString;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;

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

    public function getNewName()
    {
        return $this->newName;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\ClassLike
            && $node->name === $this->targetName->getLast()) {

            $this->code->addModification(
                $node->getAttribute('startFilePos'),
                'class ' . $node->name,
                'class ' . $this->newName->getLast()
            );
            static::$targetClass = true;

            // do not return to keep modifiy
        }

//        if ($node instanceof Name
//            && $node->toString() === $this->targetName->toString()
//        ) {
//            $this->code->addModification(
//                $node->getAttribute('startFilePos'),
//                $node->toString(),
//                $this->newName->getLast()
//            );
//        } elseif ($node instanceof Stmt\Class_) {
//        }

        return null;
    }
}
