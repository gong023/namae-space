<?php

namespace NamaeSpace\Visitor;

use NamaeSpace\MutableString;
use NamaeSpace\ReplacedCode;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;

class ReplaceVisitor extends NodeVisitorAbstract
{
    /**
     * @var Name
     */
    private $originName;

    /**
     * @var Name
     */
    private $newName;

    /**
     * @var MutableString
     */
    private $code;

    public function __construct(Name $originName, Name $newName, ReplacedCode $code)
    {
        $this->originName = $originName;
        $this->newName = $newName;
        $this->code = $code;
    }

    public function beforeTraverse(array $nodes)
    {
        $this->code->named = $this->code->stmtUseModified = false;
        $this->code->stmtNameSpacePosEnd = $this->code->stmtClassLikePosStart = $this->code->stmtUsesPosStart = null;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_) {
            if (isset($node->namespacedName)) {
                $this->code->stmtClassLikePosStart = $node->getAttribute('startFilePos') - 1;
            }
            if (isset($node->namespacedName) && $node->namespacedName->toString() === $this->originName->toString()) {
                $this->code->isTargetClass = true;
                if ($node->isAbstract()) {
                    $prefix = 'abstract class ';
                } elseif ($node->isFinal()) {
                    $prefix = 'final class ';
                } else {
                    $prefix = 'class ';
                }
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    $prefix . $node->namespacedName->getLast(),
                    $prefix . $this->newName->getLast()
                );
            } elseif ($node->extends !== null) {
                $this->addMatchedNameModification($node->extends);
            } else {
                foreach ($node->implements as $implement) {
                    $this->addMatchedNameModification($implement);
                }
            }
        } elseif ($node instanceof Stmt\Interface_) {
            if (isset($node->namespacedName)) {
                $this->code->stmtClassLikePosStart = $node->getAttribute('startFilePos') - 1;
            }
            if (isset($node->namespacedName) && $node->namespacedName->toString() === $this->originName->toString()) {
                $this->code->isTargetClass = true;
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    'interface ' . $node->namespacedName->getLast(),
                    'interface ' . $this->newName->getLast()
                );
            } else {
                foreach ($node->extends as $extend) {
                    $this->addMatchedNameModification($extend);
                }
            }
        } elseif ($node instanceof Stmt\Trait_) {
            if (isset($node->namespacedName)) {
                $this->code->stmtClassLikePosStart = $node->getAttribute('startFilePos') - 1;
            }
            if (isset($node->namespacedName) && $node->namespacedName->toString() === $this->originName->toString()) {
                $this->code->isTargetClass = true;
                $this->code->addModification(
                    $node->getAttribute('startFilePos'),
                    'trait ' . $node->namespacedName->getLast(),
                    'trait ' . $this->newName->getLast()
                );
            }
        } elseif ($node instanceof Stmt\Use_ || $node instanceof Stmt\GroupUse) {
            foreach ($node->uses as $use) {
                if (!$use->name instanceof Name || $use->name->toString() !== $this->originName->toString()) {
                    continue;
                }
                $this->code->stmtUseModified = true;
                $this->code->addModification(
                    $use->name->getAttribute('startFilePos'),
                    $use->name->toString(),
                    $this->newName->toString()
                );
            }
            if ($node->uses[0] instanceof Name) {
                $this->code->stmtUsesPosStart = $node->uses[0]->getAttribute('startFilePos') - 1;
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
                if (isset($param->type)) {
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

        if ($node instanceof Stmt\Namespace_ && $node->name instanceof Name) {
            $this->code->stmtNameSpacePosEnd = $node->name->getAttribute('startFilePos') + strlen($node->name->toString() . ";\n");
            if ($this->code->isTargetClass) {
                $removed = $node->name->toString();
                $inserted = $this->newName->slice(0, count($this->newName->parts) - 1)->toString();
                $this->code->addModification($node->name->getAttribute('startFilePos'), $removed, $inserted);
                $this->code->named = true;
            }
        }

        // NamaeSpace modifies code string itself instead of changing tree directly.
        // visit https://github.com/nikic/PHP-Parser/issues/41 to know more detail.
        return null;
    }

    /**
     * @param Node[] $nodes
     * @return null
     */
    public function afterTraverse(array $nodes)
    {
        if ($this->code->isTargetClass && !$this->code->named) {
            $inserted = "\nnamespace " . $this->newName->slice(0, count($this->newName->parts) - 1)->toString() . ";\n";
            $this->code->addModification(strlen("<?php\n"), '', $inserted);
            $this->code->named = true;
        }

        if (!$this->code->isTargetClass
            && !$this->code->stmtUseModified
            && $this->code->hasModification()
        ) {
            $this->addUseStmt($this->code->getPosToAddUseStmt());
        }

        return null;
    }

    private function addMatchedNameModification($removed)
    {
        if (!$removed instanceof Name || $removed->toString() !== $this->originName->toString()) {
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
            if (strpos($this->code->getOrigin(), '\\', $pos) === $pos) {
                $removedStr = '\\' . $removedStr;
            }
            $originStr = substr($origin, $pos, strlen($removedStr));
            if ($originStr === $removedStr) {
                return [$removedStr, $this->newName->getLast()];
            }
        }
    }

    private function addUseStmt($pos)
    {
        if ($this->code->stmtNameSpacePosEnd === null
            && $this->originName->isUnqualified()
            && $this->newName->isUnqualified()
        ) {
            $this->code->stmtUseModified = true;
            return;
        }

        $this->code->addModification($pos, '', "\nuse {$this->newName->toString()};\n");
        $this->code->stmtUseModified = true;
    }
}
