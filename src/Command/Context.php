<?php

namespace NamaeSpace\Command;

use NamaeSpace\ComposerContent;
use Symfony\Component\Console\Input\InputInterface;

abstract class Context
{
    protected $projectRoot;
    protected $userInput;
    protected $composer;
    protected $targetRealPath;

    public function __construct(
        $projectRoot,
        InputInterface $userInput,
        ComposerContent $composer
    ) {
        $this->projectRoot = $projectRoot;
        $this->userInput = $userInput;
        $this->composer = $composer;
    }

    /**
     * @return array
     */
    abstract public function getPayload();

    public function getSearchPaths()
    {
        $searchRoots = array_merge(
            $this->composer->getFileAndDirsToSearch(),
            $this->userInput->getOption('additional_paths')
        );

        $excludePaths = $this->userInput->getOption('exclude_paths');

        // destroy iterator to enqueue smoothly
        $searchPaths = [];
        foreach ($searchRoots as $search) {
            /** @var \SplFileInfo $fileInfo */
            foreach (\NamaeSpace\getIterator($this->projectRoot . '/' . $search, $excludePaths) as $fileInfo) {
                $searchPaths[] = $fileInfo->getRealPath();
            }
        }

        return $searchPaths;
    }

    public function getLoopOption()
    {
        return [
            'min_size' => 1,
            'max_size' => $this->userInput->getOption('max_process'),
        ];
    }

    public function setTargetRealPath($val)
    {
        $this->targetRealPath = $val;
        return $this;
    }

    protected function requiredInput($name)
    {
        if (($value = $this->userInput->getOption($name)) === null) {
            throw new \RuntimeException(sprintf(
                '-%1$s:--%2$s is required',
                ucfirst(substr($name, 0, 1)),
                $name
            ));
        }

        return $value;
    }

    protected function normalizeNameSpace($nameSpace)
    {
        return preg_replace('/^\\\/', '', $nameSpace);
    }
}