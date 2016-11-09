<?php

namespace NamaeSpace;

use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
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
 * @param string $filePath
 * @param string $rawOriginName
 * @param string $rawNewName
 * @return \NamaeSpace\ReplacedCode
 */
function traverseToReplace($filePath, $rawOriginName, $rawNewName)
{
    $code = ReplacedCode::create($filePath);
    $originName = new Name($rawOriginName);
    $newName = new Name($rawNewName);

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new NameResolver());
    $visitor = new ReplaceVisitor($originName, $newName, $code);
    $traverser->addVisitor($visitor);

    $stmts = \NamaeSpace\createParser()->parse($code->getOrigin());
    $traverser->traverse($stmts);

    return $code;
}

//function getMutableStringToReplace($rawCodeString, $rawOriginName, $rawNewName)
//{
//    $code = new MutableString($rawCodeString);
//    $originName = new Name($rawOriginName);
//    $newName = new Name($rawNewName);
//
//    $traverser = new NodeTraverser();
//    $traverser->addVisitor(new NameResolver());
//    $visitor = new ReplaceVisitor($originName, $newName, $code);
//    $traverser->addVisitor($visitor);
//
//    $stmts = \NamaeSpace\createParser()->parse($code->getOrigin());
//    $traverser->traverse($stmts);
//
//    return $code;
//}

function createParser()
{
    $lexer = new Lexer(['usedAttributes' => ['startFilePos']]);
    $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5, $lexer);

    return $parser;
}
