<?php

namespace NamaeSpace;

use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
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

function mergeRecursiveValues(array $array1, array $array2)
{
    $merge = [];
    array_walk_recursive($array1, function ($value) use (&$merge) {
        $merge[] = $value;
    });
    array_walk_recursive($array2, function ($value) use (&$merge) {
        $merge[] = $value;
    });

    return $merge;
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

function applyToEachFile($targetPath, callable $proc)
{
    if (is_file($targetPath) && strpos($targetPath, '.php')) {
        $proc(new SplFileInfo($targetPath), true);
        return;
    }
    $it = new RegexIterator(
        new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetPath)),
        '/^.+\.php$/i'
    );
    $cnt = iterator_count($it);
    $i = 1;
    /** @var SplFileInfo $file */
    foreach ($it as $file) {
        $proc($file, $i >= $cnt);
        $i++;
    }
}

/**
 * @param SplFileInfo $fileInfo
 * @param Name $originName
 * @param Name $newName
 * @return \NamaeSpace\ReplacedCode
 */
function traverseToReplace(SplFileInfo $fileInfo, Name $originName, Name $newName)
{
    $code = ReplacedCode::create($fileInfo);

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new NameResolver());
    $visitor = new ReplaceVisitor($originName, $newName, $code);
    $traverser->addVisitor($visitor);

    $stmts = \NamaeSpace\createParser()->parse($code->getOrigin());
    try {
        $traverser->traverse($stmts);
    } catch (Error $e) {
        throw new \RuntimeException("[{$fileInfo->getFilename()}] {$e->getMessage()}");
    }

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
