<?php

namespace NamaeSpace\Command;

use NamaeSpace\ComposerContent;
use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ReplaceCommand extends Command
{
    /**
     * @var \PHPParser\Parser\Php5
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $traverser;

    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('replace namespace')
            ->addOption('composer_json', 'C', InputOption::VALUE_OPTIONAL)
            ->addOption('additional_path', 'A', InputOption::VALUE_OPTIONAL)
            ->addOption('target_namespace', 'T', InputOption::VALUE_OPTIONAL)
            ->addOption('new_namespace', 'N', InputOption::VALUE_OPTIONAL)
            ->addOption('dry_run', 'D', InputOption::VALUE_OPTIONAL)
            ->addOption('help', 'H', InputOption::VALUE_OPTIONAL);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $lexer = new Lexer(['usedAttributes' => [
            'startLine', 'startFilePos', 'endFilePos'
        ]]);
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5, $lexer);
        $this->traverser = new NodeTraverSer();
        $this->traverser->addVisitor(new NameResolver());
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if (! $input->getOption('composer_json')) {
            if (file_exists(__DIR__ . '/../composer.json')) {
                $input->setOption('composer_json', __DIR__ . '/../composer.json');
            } else {
                $question = (new Question('composer.json path: '))
                    ->setValidator(['\\NamaeSpace\\ComposerContent', 'validateExists']);
                $input->setOption('composer_json', $helper->ask($input, $output, $question));
            }
        }

        if (! $input->getOption('target_namespace')) {
            $targetNameSpace = $helper->ask($input, $output, new Question('target name space: '));
            $input->setOption('target_namespace', $targetNameSpace);
        }

        if (! $input->getOption('new_namespace')) {
            $newNameSpace = $helper->ask($input, $output, new Question('new name space: '));
            $input->setOption('new_namespace', $newNameSpace);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetNameSpace = new Name($input->getOption('target_namespace'));
        $newNameSpace = new Name($input->getOption('new_namespace'));
        $this->traverser->addVisitor(new ReplaceVisitor($newNameSpace));

        $composerContent = ComposerContent::instantiate($input->getOption('composer_json'));

    }
}
