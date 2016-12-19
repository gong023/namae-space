<?php

namespace NamaeSpace;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class Command extends SymfonyCommand
{
    protected function communicateWithChild(
        LoopInterface $loop,
        PromiseInterface $childProcess,
        array $payload,
        $targetPath
    ) {
        $childProcess->then(function (PoolInterface $pool) use ($loop, $payload, $targetPath) {
            $iterator = \NamaeSpace\getIterator($targetPath);
            $iteratorCnt = $iterator instanceof \Traversable ? iterator_count($iterator) : 1;
            $i = 1;

            /** @var \SplFileInfo $fileInfo */
            foreach ($iterator as $fileInfo) {
                $isEnd = $i >= $iteratorCnt;
                $payload['target_real_path'] = $fileInfo->getRealPath();
                $pool->rpc(MessagesFactory::rpc('return', $payload))
                    ->then(function (Payload $payload) use ($isEnd, $pool, $loop) {
                        \NamaeSpace\write($payload['stdout']);
                        StdoutPool::$stdouts[] = $payload['stdout_pool'];
                        if ($isEnd) {
                            $pool->terminate(MessagesFactory::message());
                            $loop->stop();
                        }
                    }, function (Payload $payload) {
                        \NamaeSpace\write($payload['exception_class'] . PHP_EOL);
                        \NamaeSpace\write($payload['exception_message'] . PHP_EOL);
                    });

                $i++;
            }
        });

        $loop->run();
    }
}
