<?php

namespace NamaeSpace\Command;

use NamaeSpace\ComposerContent;
use Symfony\Component\Console\Input\InputInterface;

abstract class Context
{
    /**
     * @var string project root
     */
    protected $projectDir;

    /**
     * @var string file real path to be analyzed
     */
    protected $targetRealPath;

    /**
     * @var ComposerContent
     */
    protected $composerContent;

    /**
     * @var InputInterface
     */
    protected $input;

    public static function create(InputInterface $input)
    {
        $projectDir = static::detectProjectDir($input->getOption('composer_json'));
        $rawContent = json_decode(file_get_contents($projectDir . '/composer.json'), true);
        $composerContent = ComposerContent::instantiate($rawContent);

        return new static($projectDir, $composerContent, $input);
    }

    protected static function detectProjectDir($composerJsonPath)
    {
        if ($composerJsonPath === null) {
            if (file_exists(__DIR__ . '/../composer.json')) {
                return realpath(__DIR__ . '/../');
            } else {
                throw new \RuntimeException('-C:--composer_json path is required');
            }
        }

        $realPath = realpath($composerJsonPath);
        if (strpos($realPath, 'composer.json') && file_exists($realPath)) {
            return str_replace('composer.json', '', $realPath);
        }
        if (file_exists("$realPath/composer.json")) {
            return $realPath;
        }
        throw new \RuntimeException("composer.json doesn't exist in $realPath");
    }

    public function __construct(
        $projectDir,
        ComposerContent $composerContent,
        InputInterface $input
    ) {
        $this->projectDir = $projectDir;
        $this->composerContent = $composerContent;
        $this->input = $input;
    }

    /**
     * @return array
     */
    abstract public function payload();

    public function getExcludePaths()
    {
        return $this->input->getOption('exclude_paths');
    }

    public function getLoopOption()
    {
        return [
            'min_size' => 1,
            'maz_size' => $this->input->getOption('max_process'),
        ];
    }

    public function getSearchPaths()
    {
        return array_merge(
            $this->composerContent->getFileAndDirsToSearch(),
            $this->input->getOption('additional_paths')
        );
    }

    public function setTargetPath($value)
    {
        $this->targetRealPath = $value;
        return $this;
    }

    protected function requiredInput($name)
    {
        if (($value = $this->input->getOption($name)) === null) {
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