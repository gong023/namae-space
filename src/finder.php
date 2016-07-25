<?php

namespace NamaeSpace;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function applyToEachFile($basePath, array $targetPaths, callable $proc)
{
    foreach ($targetPaths as $targetPath) {
        $targetPath = $basePath . '/' . $targetPath;
        if (is_file($targetPath)) {
            $proc($basePath, new SplFileInfo($targetPath));
            continue;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        /** @var SplFileInfo $file */
        foreach ($it as $file) {
            if ($file->isFile() || strpos($file->getPathname(), '.php')) {
                $proc($basePath, $file);
            }
        }
    }
}
