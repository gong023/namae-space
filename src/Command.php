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
        $iterator = \NamaeSpace\getIterator($targetPath);
        $iteratorCnt = iterator_count($iterator);
        $i = 1;

        $childProcess->then(function (PoolInterface $pool) use ($loop, $payload, $targetPath, $iterator, $iteratorCnt, &$i) {
            $isEnd = $i >= $iteratorCnt;
            /** @var \SplFileInfo $fileInfo */
            foreach ($iterator as $fileInfo) {
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
    }
}