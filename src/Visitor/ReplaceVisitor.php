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
                    $this->addNameModification($use, $use->name);
                }
            }
        } elseif ($node instanceof Expr\FuncCall && $node->name->isFullyQualified()) {
            $funcNameSpace = $node->name->slice(0, count($node->name->parts) - 1)->toString();
            if ($funcNameSpace === $this->targetName->toString()) {
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    '\\' . $funcNameSpace,
                    '\\' . $this->newName->toString()
                );
            }
        } elseif ($node instanceof Expr\New_ && $this->isNameMatched($node->class)) {
            $this->addNameModification($node, $node->class, 'new');
        }

        return null;
    }

    private function isNameMatched(Name $name)
    {
        return $name->toString() === $this->targetName->toString();
    }

    private function addNameModification(NodeAbstract $node, Name $name, $prefix = null)
    {
        $removed = $name->toString();
        $inserted = $this->newName->toString();
        if ($prefix !== null) {
            // NameResolver doesn't append first backslash so MutableString position shifts to one right
            $prefix .= count($name->parts) > 1 ? ' \\' : ' ';
            $removed = $prefix . $removed;
            $inserted = $prefix . $inserted;
        }

        $this->code->addModification($node->getAttribute('startFilePos'), $removed, $inserted);
    }
}
