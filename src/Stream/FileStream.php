<?php

namespace NamaeSpace\Stream;

use NamaeSpace\Exception\FileNotReadableException;
use NamaeSpace\Exception\RuntimeException;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\Finder;

class FileStream
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct(Finder $finder, Standard $prettyPrinter)
    {
        $this->finder = $finder;
        $this->prettyPrinter = $prettyPrinter;
    }

    public function findFiles($directory)
    {
        return $this->finder->files()->in($directory);
    }

    public function put($fileName, $data)
    {
        return file_put_contents($fileName, $data);
    }

    /**
     * @param $fileName
     * @param \PhpParser\Node[] $nodes
     */
    public function putNodes($fileName, array $nodes)
    {
        $code = $this->prettyPrinter->prettyPrintFile($nodes);
        if (! $this->put($fileName, $code)) {
            throw new RuntimeException('failed to write code in ' . $fileName);
        }
    }

    public function get($fileName)
    {
        if (! is_readable($fileName)) {
            throw new FileNotReadableException('failed to read ' . $fileName);
        }

        return file_get_contents($fileName);
    }

    public function getLines($fileName)
    {
        $file = new \SplFileObject($fileName);
        $lines = [];
        while (! $file->eof()) {
            $lines[$file->key()] = $file->fgets();
        }

        return $lines;
    }

    public function getPrettyPrinter()
    {
        return $this->prettyPrinter;
    }
}
