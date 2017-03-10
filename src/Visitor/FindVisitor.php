<?php

namespace NamaeSpace\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class FindVisitor extends NodeVisitorAbstract
{
    public $foundString;

    private $findName;
    private $realPath;

    public function __construct($findName, $realPath)
    {
        $this->findName = $findName;
        $this->realPath = $realPath;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Name) {
            if ($node->toString() === $this->findName) {
                $line = $node->getLine();
                $this->foundString .= '<comment>' . $this->realPath . ":L$line</comment>\n";
                $file = new \SplFileObject($this->realPath);
                $startLine = ($line - 4) < 0 ? 0 : $line - 4;
                $file->seek($startLine);
                for ($i = -3; $i <= 3; $i++) {
                    if ($file->key() === $line - 1) {
                        $this->foundString .= '<info>' . $file->current() . '</info>';
                    } else {
                        $this->foundString .= $file->current();
                    }
                    $file->next();
                }
            }
        }

        return null;
    }
}
