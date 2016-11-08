<?php

namespace NamaeSpace\ChildProcess\Replace;

use NamaeSpace\MutableString;
use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Node\Name;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class Overwrite implements  ChildInterface
{
    /**
     * @var Payload
     */
    private $payload;

    public function __construct(Payload $payload)
    {
        $this->payload = $payload;
    }

    public function process()
    {
        /** @var MutableString $code */
        $code = \NamaeSpace\traverseToReplace(
            file_get_contents($this->payload['real_path']),
            $this->payload['origin_name'],
            $this->payload['new_name']
        );

        if (ReplaceVisitor::$targetClass) {
            ReplaceVisitor::$targetClass = false;
            $fileDir = $this->payload['base_path'] . '/' . $this->payload['replace_dir'];
            $newName = new Name($this->payload['new_name']);
            $outputFilePath = "$fileDir/{$newName->getLast()}.php";
            @mkdir($fileDir, 0755, true);
            file_put_contents($outputFilePath, $code->getModified());
            @unlink($this->payload['real_path']);
            @rmdir($this->payload['path']); // $fileInfo->getPath()
        } else {
            file_put_contents($this->payload['real_path'], $code->getModified());
        }
    }

    /**
     * @param Messenger $messenger
     * @param LoopInterface $loop
     * @return void
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('return', function (Payload $payload) {
            try {
                (new self($payload))->process();
                return \React\Promise\resolve();
            } catch (\Exception $e) {
                return \React\Promise\reject([
                    'sent_payload'      => $payload->getPayload(),
                    'exception_class'   => get_class($e),
                    'exception_message' => $e->getMessage(),
                ]);
            }
        });
    }
}