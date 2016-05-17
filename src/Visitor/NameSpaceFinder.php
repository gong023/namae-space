<?php

namespace NamaeSpace\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * as quick as possible
 */
class NameSpaceFinder extends NodeVisitorAbstract
{
    public static $filePath;

    /**
     * @var Name
     */
    private $findNameSpace;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(Name $findNameSpace, OutputInterface $output)
    {
        $this->findNameSpace = $findNameSpace;
        $this->output = $output;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Name) {
            if ($node->toString() === $this->findNameSpace->toString()) {
                $line = $node->getLine();
                $this->output->writeln('<comment>' . self::$filePath . ":L$line</comment>");
                $file = new \SplFileObject(self::$filePath);
                $startLine = ($line - 4) < 0 ? 0 : $line - 4;
                $file->seek($startLine);
                for ($i = -3; $i <= 3; $i++) {
                    if ($file->key() === $line - 1) {
                        $this->output->write('<info>' . $file->current() . '</info>');
                    } else {
                        echo $file->current();
                    }
                    $file->next();
                }
            }
        }

        return null;
    }
}
