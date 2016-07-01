<?php

namespace NamaeSpace;

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

    public function getClassmapDirs()
    {
        $classmap = array_merge(
            array_values((array)$this->content->autoload->classmap),
            array_values((array)$this->content->autoload_dev->classmap)
        );

        return $this->concatWithRealPath($classmap);
    }

    public function getFiles()
    {
        $files = array_merge(
            array_values((array)$this->content->autoload->files),
            array_values((array)$this->content->autoload_dev->files)
        );

        return $this->concatWithRealPath($files);
    }

    public function getPsr4Dirs()
    {
        $psr4 = array_merge((array)$this->content->autoload->psr_4, (array)$this->content->autoload_dev->psr_4);
        $psr4 = $this->concatWithRealPath($psr4);

        return array_unique(array_values($psr4));
    }


    private function concatWithRealPath($dirs)
    {
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
            return $input;
        }

        throw new \RuntimeException("composer.json doesn't exist in $input");
    }
    
    public static function instantiate($fileName)
    {
        $raw = json_decode(file_get_contents($fileName, true));
        if (null === $raw) {
            throw new \RuntimeException('failed to parse composer.json: ' . $fileName);
        }
        $realDirPath = dirname(realpath($fileName));
        
        return new static($realDirPath, new NullableArray($raw));
    }
}
