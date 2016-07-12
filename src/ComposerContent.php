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

    private function concatWithRealPath(array $autoload, array $autoloadDev = [])
    {
        $dirs = array_merge(
            $this->flatternValues($autoload),
            $this->flatternValues($autoloadDev)
        );

        return array_map(function ($dir) {
            return $this->realDirPath . '/' . $dir;
        }, $dirs);
    }

    private function flatternValues(array $array)
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
            return $input;
        }

        throw new \RuntimeException("composer.json doesn't exist in $input");
    }
    
    public static function instantiate($fileName)
    {
        $raw = json_decode(file_get_contents($fileName, true));
        if ($raw === null) {
            throw new \RuntimeException('failed to parse composer.json: ' . $fileName);
        }
        $realDirPath = dirname(realpath($fileName));
        
        return new static($realDirPath, new NullableArray($raw));
    }
}
