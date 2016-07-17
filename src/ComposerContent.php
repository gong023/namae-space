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
            arrayFlatten((array)$this->content->autoload->psr_0),
            arrayFlatten((array)$this->content->autoload_dev->psr_0)
        );
    }

    public function getPsr4Dirs()
    {
        return array_merge(
            arrayFlatten((array)$this->content->autoload->psr_4),
            arrayFlatten((array)$this->content->autoload_dev->psr_4)
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
