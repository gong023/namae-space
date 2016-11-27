<?php

namespace NamaeSpace;

use PhpParser\Node\Name;

class ComposerContent
{
    /**
     * @var NullableArray
     */
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getDirsToReplace(array $nameSpaceParts)
    {
        $dirsToReplace = [];
        $matchLength = 0;
        for ($i = 1; $i < count($nameSpaceParts); $i++) {
            $key = joinToString('\\', $nameSpaceParts, $i);
            $r = array_merge(
                arrayFlatten((array)$this->content['autoload']['psr-4'][$key]),
                arrayFlatten((array)$this->content['autoload']['psr-0'][$key]),
                arrayFlatten((array)$this->content['autoload-dev']['psr-4'][$key]),
                arrayFlatten((array)$this->content['autoload-dev']['psr-0'][$key])
            );
            $c = count($r);
            if ($c >= $matchLength) {
                $dirsToReplace = array_map(function ($path) use ($nameSpaceParts) {
                    $parts = $nameSpaceParts;
                    array_shift($parts);
                    $path = preg_replace('/\/$/', '', $path);

                    return $path . '/' . joinToString('/', $parts, count($parts) - 1);
                }, $r);
                $matchLength = $c;
            }
        }

        return $dirsToReplace;
    }

    public function getClassmapValues()
    {
        return mergeRecursiveValues(
            (array)$this->content['autoload']['classmap'],
            (array)$this->content['autoload-dev']['classmap']
        );
    }

    public function getFilesValues()
    {
        return mergeRecursiveValues(
            (array)$this->content['autoload']['files'],
            (array)$this->content['autoload-dev']['files']
        );
    }

    public function getIncludePathDirs()
    {
        return arrayFlatten((array)$this->content['include-path']);
    }

    public function getPsr0Dirs()
    {
        return mergeRecursiveValues(
            (array)$this->content['autoload']['psr-0'],
            (array)$this->content['autoload-dev']['psr-0']
        );
    }

    public function getPsr4Dirs()
    {
        return mergeRecursiveValues(
            (array)$this->content['autoload']['psr-4'],
            (array)$this->content['autoload-dev']['psr-4']
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

    public static function getRealDir($input = null)
    {
        if ($input === null) {
            if (file_exists(__DIR__ . '/../composer.json')) {
                return realpath(__DIR__ . '/../');
            } else {
                throw new \RuntimeException('composer_json path is required');
            }
        }

        $realPath = realpath($input);
        if (strpos($realPath, 'composer.json') && file_exists($realPath)) {
            return str_replace('composer.json', '', $realPath);
        }
        if (file_exists("$realPath/composer.json")) {
            return $realPath;
        }

        throw new \RuntimeException("composer.json doesn't exist in $realPath");
    }

    public static function instantiate(array $raw)
    {
        if (isset($raw['autoload']) && isset($raw['autoload']['psr-0'])) {
            $replaced = [];
            foreach ($raw['autoload']['psr-0'] as $namespace => $dir) {
                $replaced[str_replace('_', '\\', $namespace)] = $dir;
            }
            $raw['autoload']['psr-0'] = $replaced;
        }

        if (isset($raw['autoload-dev']) && isset($raw['autoload-dev']['psr-0'])) {
            $replaced = [];
            foreach ($raw['autoload-dev']['psr-0'] as $namespace => $dir) {
                $replaced[str_replace('_', '\\', $namespace)] = $dir;
            }
            $raw['autoload-dev']['psr-0'] = $replaced;
        }

        return new static(new NullableArray($raw));
    }
}
