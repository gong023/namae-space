<?php

namespace NamaeSpace\ChildProcess;

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class Find implements ChildInterface
{
    /**
     * @param Messenger $messenger
     * @param LoopInterface $loop
     * @return void
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('return', function (Payload $payload) {
            try {
                $targetRealPath = $payload['target_real_path'];
                $findName = $payload['find_name'];
                $codeString = file_get_contents($targetRealPath);
                $stdoutPool = \NamaeSpace\traverseToFind($findName, $codeString, $targetRealPath);

                return \React\Promise\resolve([
                    'input'       => $payload,
                    'stdout_pool' => $stdoutPool,
                ]);
            } catch (\Exception $e) {
                return \React\Promise\reject([
                    'input'             => $payload->getPayload(),
                    'exception_class'   => get_class($e),
                    'exception_message' => $e->getMessage(),
                ]);
            }
        });
    }
}
