<?php

namespace NamaeSpace\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TurmericSpice\Container\InvalidAttributeException;

/**
 * @property \TurmericSpice\Container $attributes
 */
class Argument
{
    /**
     * @var QuestionHelper
     */
    protected $helper;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;
    
    public function setHelper(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $this->helper = $helper;
        $this->input  = $input;
        $this->output = $output;
        
        return $this;
    }

    public function ask($key)
    {
        try {
            $default = $this->attributes->mustHave($key)->asString();
            $question = new Question("{$key}[default:{$default}]: ", $default);
        } catch (InvalidAttributeException $e) {
            $question = new Question("$key: ");
        }

        $value = $this->helper->ask($this->input, $this->output, $question);
        if ($value) {
            $this->attributes->set($key, $value);
        }

        return $this;
    }
}
