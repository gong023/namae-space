<?php

namespace NamaeSpace;

use NamaeSpace\Command\Context;
use React\EventLoop\Factory as EventLoopFactory;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;

class Command extends SymfonyCommand
{
    protected function executeChild(
        $childName,
        Context $context,
        OutputInterface $output
    ) {
        $promises = [];
        $loop = EventLoopFactory::create();
        $child = Flexible::createFromClass($childName, $loop, $context->getLoopOption());
        $searchPaths = $context->getSearchPaths();

        $progressBar = new ProgressBar($output, count($searchPaths));
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%% %filename%");
        $progressBar->setMessage('', 'filename');
        $progressBar->start();

        foreach ($searchPaths as $searchPath) {
            $payload = $context->setTargetRealPath($searchPath)->getPayload();

            $promises[] = $child->then(function (PoolInterface $pool) use ($payload, $progressBar, $output) {
                return $pool->rpc(MessagesFactory::rpc('return', $payload))
                    ->then(function (Payload $payload) use ($progressBar) {
                        $progressBar->setMessage($payload['input']['target_real_path'], 'filename');
                        $progressBar->advance();
                        StdoutPool::$stdouts[] = $payload['stdout_pool'];
                    }, function (Payload $payload) use ($output) {
                        $output->writeln($payload['exception_class']);
                        $output->writeln($payload['exception_message']);
                    });
            });
        }

        \React\Promise\all($promises)
            ->then(function () use ($loop, $progressBar, $output) {
                $loop->stop();
                $progressBar->setMessage('', 'filename');
                $progressBar->finish();
                $output->writeln("\n");
                foreach (StdoutPool::$stdouts as $stdout) {
                    $output->writeln($stdout);
                }
            });

        $loop->run();
    }
}
