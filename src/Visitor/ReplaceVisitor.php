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
                    $this->addNameModification($use->name);
                }
            }
        } elseif ($node instanceof Expr\FuncCall && $node->name instanceof Name && $node->name->isFullyQualified()) {
            /** @var Name $funcNameSpace */
            $funcNameSpace = $node->name->slice(0, count($node->name->parts) - 1);
            if ($funcNameSpace->toString() === $this->targetName->toString()) {
                $this->addNameModification($funcNameSpace);
            }
        } elseif ($node instanceof Expr\New_ && $this->isNameMatched($node->class)
            || $node instanceof Expr\Instanceof_ && $this->isNameMatched($node->class)
        ) {
            $this->addNameModification($node->class);
        } elseif ($node instanceof Stmt\ClassMethod) {
            foreach ($node->params as $param) {
                if ($this->isNameMatched($param->type)) {
                    $this->addNameModification($param->type);
                }
            }
        } elseif ($node instanceof Stmt\Catch_) {
            foreach ($node->types as $type) {
                if ($this->isNameMatched($type)) {
                    $this->addNameModification($type);
                }
            }
        }

        return null;
    }

    private function isNameMatched(Name $name)
    {
        return $name->toString() === $this->targetName->toString();
    }

    private function addNameModification(Name $removed)
    {
        $inserted = $this->newName->toString();
        $pos = $removed->getAttribute('startFilePos');
        $removed = $removed->toString();
        // NameResolver doesn't append first backslash so MutableString position shifts to one right.
        // PhpParser\Node\Name#isFullyQualified doesn't work to judge we should add backslash or not.
        if (strpos($this->code->getOrigin(), '\\', $pos) === $pos) {
            $removed = '\\' . $removed;
            $inserted = '\\' . $inserted;
        }

        $this->code->addModification($pos, $removed, $inserted);
    }
}
