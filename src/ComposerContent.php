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

    public function getPsr4Dirs()
    {
        $dirs = (array)$this->content->autoload->psr_4;
        $dirs = array_map(function ($dir) {
            return $this->realDirPath . '/' . $dir;
        }, $dirs);

        return array_unique(array_values($dirs));
    }

    public function get()
    {
        return (array)$this->content->autoload->psr_0
            + (array)$this->content->autoload->psr_4
            + (array)$this->content->autoload->classmap
            + (array)$this->content->autoload->include_path;
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
