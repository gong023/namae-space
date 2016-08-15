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
        if ($node instanceof Stmt\Class_) {
            if (isset($node->namespacedName) && $node->namespacedName->toString() === $this->targetName->toString()) {
                static::$targetClass = true;
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    'class ' . $node->namespacedName->getLast(),
                    'class ' . $this->newName->getLast()
                );
            } elseif ($node->extends !== null) {
                $this->addMatchedNameModification($node->extends);
            }
            foreach ($node->implements as $implement) {
                $this->addMatchedNameModification($implement);
            }
        } elseif ($node instanceof Stmt\Interface_) {
            if ($node->name === $this->targetName->getLast()) {
                static::$targetClass = true;
            }
            foreach ($node->extends as $extend) {
                $this->addMatchedNameModification($extend);
            }
        } elseif ($node instanceof Stmt\Trait_) {
            if ($node->name === $this->targetName->getLast()) {
                static::$targetClass = true;
            }
        } elseif ($node instanceof Stmt\Use_ || $node instanceof Stmt\GroupUse) {
            foreach ($node->uses as $use) {
                $this->addMatchedNameModification($use->name);
            }
        } elseif ($node instanceof Expr\FuncCall && $node->name instanceof Name && $node->name->isFullyQualified()) {
            /** @var Name $funcNameSpace */
            $funcNameSpace = $node->name->slice(0, count($node->name->parts) - 1);
            $this->addMatchedNameModification($funcNameSpace);
        } elseif ($node instanceof Expr\New_
            || $node instanceof Expr\Instanceof_
            || $node instanceof Expr\StaticCall
            || $node instanceof Expr\StaticPropertyFetch
            || $node instanceof Expr\ClassConstFetch
        ) {
            $this->addMatchedNameModification($node->class);
        } elseif ($node instanceof Stmt\ClassMethod || $node instanceof Stmt\Function_ || $node instanceof Expr\Closure) {
            foreach ($node->params as $param) {
                if ($param->type instanceof Name) {
                    $this->addMatchedNameModification($param->type);
                }
            }
        } elseif ($node instanceof Stmt\Catch_) {
            foreach ($node->types as $type) {
                $this->addMatchedNameModification($type);
            }
        } elseif ($node instanceof Stmt\TraitUse) {
            foreach ($node->traits as $trait) {
                $this->addMatchedNameModification($trait);
            }
        }

        if (self::$targetClass && $node instanceof Stmt\Namespace_ && $node->name instanceof Name) {
            $removed = $node->name->toString();
            $inserted = $this->newName->slice(0, count($this->newName->parts) - 1)->toString();
            $this->code->addModification($node->name->getAttribute('startFilePos'), $removed, $inserted);
        }

        return null;
    }

    private function addMatchedNameModification(Name $removed)
    {
        if ($removed->toString() !== $this->targetName->toString()) {
            return;
        }

        $pos = $removed->getAttribute('startFilePos');
        list($removedStr, $insertedStr) = $this->getModifyStrings($pos, $removed);

        $this->code->addModification($pos, $removedStr, $insertedStr);
    }

    private function getModifyStrings($pos, Name $removed)
    {
        $origin = $this->code->getOrigin();
        for ($i = -1; $i >= -count($removed->parts); $i--) {
            $removedStr = $removed->slice($i)->toString();
            // NameResolver doesn't append first backslash so MutableString position shifts to one right.
            // Functions such as Name#isFullyQualified don't work to know we should add backslash or not.
            $needBackSlash = false;
            if (strpos($this->code->getOrigin(), '\\', $pos) === $pos) {
                $removedStr = '\\' . $removedStr;
                $needBackSlash = true;
            }
            $originStr = substr($origin, $pos, strlen($removedStr));
            if ($originStr === $removedStr) {
                $insertedStr = $needBackSlash ? '\\' . $this->newName->slice($i)->toString() : $this->newName->slice($i)->toString();
                return [$removedStr, $insertedStr];
            }
        }
    }
}
