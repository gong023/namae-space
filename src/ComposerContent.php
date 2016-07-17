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

    public function getClassmapValues()
    {
        return $this->concatWithRealPath(
            (array)$this->content->autoload->classmap,
            (array)$this->content->autoload_dev->classmap
        );
    }

    public function getFilesValues()
    {
        return $this->concatWithRealPath(
            (array)$this->content->autoload->files,
            (array)$this->content->autoload_dev->files
        );
    }

    public function getIncludePathDirs()
    {
        return $this->concatWithRealPath((array)$this->content->include_path);
    }

    public function getPsr0Dirs()
    {
        // TODO: treat UniqueGlobalClass
        $dirs = $this->concatWithRealPath(
            (array)$this->content->autoload->psr_0,
            (array)$this->content->autoload_dev->psr_0
        );

        return array_unique($dirs);
    }

    public function getPsr4Dirs()
    {
        return $this->concatWithRealPath(
            (array)$this->content->autoload->psr_4,
            (array)$this->content->autoload_dev->psr_4
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

    public function getDirsToReplace(Name $nameSpace)
    {
        $dirsToReplace = [];
        $matchLength = 0;
        for ($i = 1; $i < count($nameSpace->parts); $i++) {
            $key = joinToString('_', $nameSpace->parts, $i);
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

                    return $path . '/' . joinToString('/', $parts, count($parts) - 1);
                }, $r);
                $matchLength = $c;
            }
        }

        return array_values($dirsToReplace);
    }

    private function concatWithRealPath(array $autoload, array $autoloadDev = [])
    {
        $dirs = array_merge(arrayFlatten($autoload), arrayFlatten($autoloadDev));

        return array_map(function ($dir) {
            return $this->realDirPath . '/' . $dir;
        }, $dirs);
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
        $raw = json_decode(file_get_contents(self::validateExists($fileName)), true);
        if ($raw === null) {
            throw new \RuntimeException('failed to parse composer.json: ' . $fileName);
        }
        $realDirPath = dirname(realpath($fileName));
        
        return new static($realDirPath, new NullableArray($raw));
    }
}
