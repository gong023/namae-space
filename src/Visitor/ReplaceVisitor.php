<?php

namespace NamaeSpace\Visitor;

use NamaeSpace\MutableString;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeAbstract;
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
        if ($node instanceof Stmt\ClassLike && $node->name === $this->targetName->getLast()) {
            $this->code->addModification(
                $node->getAttribute('startFilePos'),
                'class ' . $node->name,
                'class ' . $this->newName->getLast()
            );
            static::$targetClass = true;
        } elseif ($node instanceof Stmt\Use_ || $node instanceof Stmt\GroupUse) {
            foreach ($node->uses as $use) {
                if ($this->isNameMatched($use->name)) {
                    $this->addNameModification($use->name->getAttribute('startFilePos'), $use->name->toString());
                }
            }
        } elseif ($node instanceof Expr\FuncCall && $node->name->isFullyQualified()) {
            $funcNameSpace = $node->name->slice(0, count($node->name->parts) - 1)->toString();
            if ($funcNameSpace === $this->targetName->toString()) {
                $this->addNameModification($node->getAttribute('startFilePos'), $funcNameSpace);
            }
        } elseif ($node instanceof Expr\New_ && $this->isNameMatched($node->class)
            || $node instanceof Expr\Instanceof_ && $this->isNameMatched($node->class)
        ) {
            $this->addNameModification($node->class->getAttribute('startFilePos'), $node->class->toString());
        } elseif ($node instanceof Stmt\Catch_) {
            foreach ($node->types as $type) {
                if ($this->isNameMatched($type)) {
                    $this->addNameModification($type->getAttribute('startFilePos'), $type->toString());
                }
            }
        }

        return null;
    }

    private function isNameMatched(Name $name)
    {
        return $name->toString() === $this->targetName->toString();
    }

    private function addNameModification($pos, $removed)
    {
        $inserted = $this->newName->toString();
        // NameResolver doesn't append first backslash so MutableString position shifts to one right.
        // PhpParser\Node\Name#isFullyQualified doesn't work to judge we should add backslash or not.
        if (strpos($this->code->getOrigin(), '\\', $pos) === $pos) {
            $removed = '\\' . $removed;
            $inserted = '\\' . $inserted;
        }

        $this->code->addModification($pos, $removed, $inserted);
    }
}
