<?php

namespace NamaeSpace\ChildProcess\Replace;

use NamaeSpace\MutableString;
use NamaeSpace\Visitor\ReplaceVisitor;
use PhpParser\Node\Name;
use React\EventLoop\LoopInterface;
use SebastianBergmann\Diff\Differ;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class DryRun implements ChildInterface
{
    /**
     * @var Payload
     */
    private $payload;

    /**
     * @var Differ
     */
    private $differ;

    private function __construct(Payload $payload, Differ $differ)
    {
        $this->payload = $payload;
        $this->differ = $differ;
    }

    public function process()
    {
        /** @var MutableString $code */
        $code = \NamaeSpace\traverseToReplace(
            file_get_contents($this->payload['real_path']),
            $this->payload['origin_name'],
            $this->payload['new_name']
        );

        if ($code->hasModification()) {
            \NamaeSpace\writeln('<info>' . $this->payload['filename'] . '</info>');
            \NamaeSpace\writeln($this->differ->diff($code->getOrigin(), $code->getModified()));
        }
    }

    /**
     * @param Messenger $messenger
     * @param LoopInterface $loop
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('return', function (Payload $payload) {
            $differ = new Differ("--- Original\n+++ New\n", false);

            try {
                (new self($payload, $differ))->process();
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
