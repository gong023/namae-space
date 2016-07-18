<?php

namespace NamaeSpace;

use PhpParser\Node\Name;

class ComposerContent
{
    /**
     * @var string
     */
    private $realDirPath;

    /**
     * @var NullableArray
     */
    private $content;

    public function __construct($realDirPath, NullableArray $content)
    {
        $this->content = $content;
        $this->realDirPath = $realDirPath;
    }

    public function getReadDirPath()
    {
        return $this->realDirPath;
    }

    public function getDirsToReplace(Name $nameSpace)
    {
        $dirsToReplace = [];
        $matchLength = 0;
        for ($i = 1; $i < count($nameSpace->parts); $i++) {
            $key = $this->joinToString('_', $nameSpace->parts, $i);
            $r = array_merge(
                (array)$this->content->autoload->psr_4->{$key},
                (array)$this->content->autoload->psr_0->{$key},
                (array)$this->content->autoload_dev->psr_4->{$key},
                (array)$this->content->autoload_dev->psr_0->{$key}
            );
            $c = count($r);
            if ($c >= $matchLength) {
                $dirsToReplace = array_map(function ($path) use ($nameSpace) {
                    $parts = $nameSpace->parts;
                    array_shift($parts);
                    $path = preg_replace('/\/$/', '', $path);

                    return $path . '/' . $this->joinToString('/', $parts, count($parts) - 1);
                }, $r);
                $matchLength = $c;
            }
        }

        return array_values($dirsToReplace);
    }

    public function getClassmapValues()
    {
        return array_merge(
            array_values((array)$this->content->autoload->classmap),
            array_values((array)$this->content->autoload_dev->classmap)
        );
    }

    public function getFilesValues()
    {
        return array_merge(
            array_values((array)$this->content->autoload->files),
            array_values((array)$this->content->autoload_dev->files)
        );
    }

    public function getIncludePathDirs()
    {
        return array_values((array)$this->content->include_path);
    }

    public function getPsr0Dirs()
    {
        return array_merge(
            $this->arrayFlatten((array)$this->content->autoload->psr_0),
            $this->arrayFlatten((array)$this->content->autoload_dev->psr_0)
        );
    }

    public function getPsr4Dirs()
    {
        return array_merge(
            $this->arrayFlatten((array)$this->content->autoload->psr_4),
            $this->arrayFlatten((array)$this->content->autoload_dev->psr_4)
        );
    }

    public function getFileAndDirsToSearch()
    {
        $paths = array_merge(
            $this->getPsr4Dirs(),
            $this->getPsr0Dirs(),
            $this->getClassmapValues(),
            $this->getFilesValues(),
            $this->getIncludePathDirs()
        );

        return array_unique($paths);
    }

    private function joinToString($glue, $pieces, $length)
    {
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $pieces[$i] . $glue;
        }

        return $str;
    }

    private function arrayFlatten(array $array)
    {
        $values = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        foreach ($iterator as $value) {
            $values[] = $value;
        }

        return $values;
    }

    public static function validateExists($input)
    {
        if (file_exists($input) && strpos($input, 'composer.json')) {
            return $input;
        }
        $input = preg_replace('/\/$/', '', $input);
        if (file_exists("$input/composer.json")) {
            return "$input/composer.json";
        }

        throw new \RuntimeException("composer.json doesn't exist in $input");
    }

    public static function instantiate($fileName)
    {
        // filename is not validated if filename is specified by -C
        $fileName = self::validateExists($fileName);
        $raw = json_decode(file_get_contents($fileName), true);
        if ($raw === null) {
            throw new \RuntimeException('failed to parse composer.json: ' . $fileName);
        }
        $realDirPath = dirname(realpath($fileName));

        return new static($realDirPath, new NullableArray($raw));
    }
}
