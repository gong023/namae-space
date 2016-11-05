<?php

namespace NamaeSpace;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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
