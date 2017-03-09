<?php

namespace NamaeSpace;

use React\EventLoop\Factory as EventLoopFactory;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;

class Command extends SymfonyCommand
{
    protected function executeChild(
        $childName,
        array $searchPaths,
        $loopOption,
        $payload
    ) {
        $promises = [];
        $loop = EventLoopFactory::create();
        $child = Flexible::createFromClass($childName, $loop, $loopOption);
        foreach ($searchPaths as $searchPath) {
            $payload['target_real_path'] = $searchPath;
            $promises[] = $child->then(function (PoolInterface $pool) use ($loop, $payload) {
                return $pool->rpc(MessagesFactory::rpc('return', $payload))
                    ->then(function (Payload $payload) use ($pool) {
                        \NamaeSpace\write($payload['stdout']);
                        StdoutPool::$stdouts[] = $payload['stdout_pool'];
                    }, function (Payload $payload) {
                        \NamaeSpace\write($payload['exception_class'] . PHP_EOL);
                        \NamaeSpace\write($payload['exception_message'] . PHP_EOL);
                    });
            });
        }

        \React\Promise\all($promises)
            ->then(function () use ($loop) {
                $loop->stop();
                StdoutPool::dump();
            });

        $loop->run();
    }
}
