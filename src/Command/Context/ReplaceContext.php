<?php

namespace NamaeSpace\Command\Context;

use NamaeSpace\Command\Context;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ReplaceContext extends Context
{
    private $originName;
    private $newName;
    private $replaceDir;

    /**
     * @return array
     */
    public function getPayload()
    {
        return [
            'project_dir'      => $this->projectRoot,
            'origin_name'      => $this->originName,
            'new_name'         => $this->newName,
            'replace_dir'      => $this->replaceDir,
            'target_real_path' => $this->targetRealPath,
        ];
    }

    public function setOriginNameFromInput()
    {
        $this->originName = $this->normalizeNameSpace($this->requiredInput('origin_namespace'));
        return $this;
    }

    public function setNewNameFromInput()
    {
        $this->newName = $this->normalizeNameSpace($this->requiredInput('new_namespace'));
        return $this;
    }

    public function setReplaceDirFromInput()
    {
        $this->replaceDir = $this->userInput->getOption('replace_dir');
        if ($this->replaceDir === null || !is_dir($this->replaceDir)) {
            throw new InvalidReplaceDirException();
        }
        return $this;
    }

    public function replaceDirFallback(QuestionHelper $helper, OutputInterface $output)
    {
        $replaceDirs = $this->composer->getDirsToReplace(explode('\\', $this->newName));
        $dirsCount = count($replaceDirs);
        if ($dirsCount === 0) {
            throw new \RuntimeException('base dir is not found to put ' . $this->newName . '.php');
        } elseif ($dirsCount === 1) {
            $this->replaceDir = $replaceDirs[0];
        } else {
            $question = new ChoiceQuestion(
                'which dir do you use to put ' . $this->newName . '.php',
                $replaceDirs
            );
            $this->replaceDir = $helper->ask($this->userInput, $output, $question);
        }

        return $this;
    }

    public function isDryRun()
    {
        return $this->userInput->getOption('dry_run');
    }
}
