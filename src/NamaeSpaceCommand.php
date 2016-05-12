<?php

namespace NamaeSpace;

use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use PhpParser\ParserFactory;

class NamaeSpaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('namaespace')
            ->setDescription('interactive command to change namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('find path: ');
        $findPath = $helper->ask($input, $output, $question);
        $finder = new Finder();

        $question = new Question('before namespace: ');
        $beforeNameSpace = $helper->ask($input, $output, $question);
        $question = new Question('after namespace: ');
        $afterNameSpace = $helper->ask($input, $output, $question);

        // test
        $findPath = '/vagrant/ghq/github.com/gong023/Ayaml/src/';
        $fileName = 'Ayaml.php';
        $beforeNameSpace = 'Ayaml\\Container';
        $afterNameSpace = 'Ayaml\\Container2';

        // boforeFileName, afterFileName

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $traverser = new NodeTraverSer();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new NameSpaceConverter(new Name($beforeNameSpace), new Name($afterNameSpace)));
        $prettyPrinter = new Standard();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files()->in($findPath) as $file) {
            $absoluteFilePathName = $file->getRealPath();
            if (preg_match('/vendor/', $absoluteFilePathName)) {
                continue;
            }
            if ($fileName && $fileName !== $file->getFilename()) {
                continue;
            }

            $output->writeln($file->getFilename());
            $stmts = $parser->parse(file_get_contents($absoluteFilePathName));
            $stmts = $traverser->traverse($stmts);

            $code = $prettyPrinter->prettyPrintFile($stmts);

            echo $code;
        }
    }
}
