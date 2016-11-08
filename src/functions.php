<?php

namespace NamaeSpace;

use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Output\ConsoleOutput;

function joinToString($glue, $pieces, $length)
{
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $pieces[$i] . $glue;
    }

    return $str;
}

function arrayFlatten(array $array)
{
    $values = [];
    $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
    foreach ($iterator as $value) {
        $values[] = $value;
    }

    return $values;
}

function applyToEachFile($basePath, array $targetPaths, callable $proc)
{
    foreach ($targetPaths as $targetPath) {
        $targetPath = $basePath . '/' . $targetPath;
        if (is_file($targetPath) && strpos($targetPath, '.php')) {
            $proc($basePath, new SplFileInfo($targetPath));
            continue;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        /** @var SplFileInfo $file */
        foreach ($it as $file) {
            if ($file->isFile() && strpos($file->getPathname(), '.php')) {
                $proc($basePath, $file);
            }
        }
    }
}

function writeln($string)
{
    /** @var ConsoleOutput $consoleOutput */
    static $consoleOutput;
    if ($consoleOutput === null) {
        $consoleOutput = new ConsoleOutput();
    }
    $consoleOutput->writeln($string);
}

/**
 * @param $rawCode
 * @param Name $originName
 * @param Name $newName
 * @return MutableString
 */
function traverseToReplace($rawCode, Name $originName, Name $newName)
{
    $code = new MutableString($rawCode);

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new NameResolver());
    $visitor = new ReplaceVisitor($originName, $newName, $code);
    $traverser->addVisitor($visitor);

    $stmts = \NamaeSpace\createParser()->parse($rawCode);
    $traverser->traverse($stmts);

    return $code;
}

function createParser()
{
    $lexer = new Lexer(['usedAttributes' => ['startFilePos']]);
    $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5, $lexer);

    return $parser;
}