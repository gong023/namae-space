<?php

namespace NamaeSpace\ChildProcess\Replace;

use NamaeSpace\MutableString;
use PhpParser\Node\Name;
use React\EventLoop\LoopInterface;
use SebastianBergmann\Diff\Differ;
use SplFileInfo;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class DryRun implements ChildInterface
{
    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    /**
     * @var Name
     */
    private $originName;

    /**
     * @var Name
     */
    private $newName;

    private function __construct(
        SplFileInfo $fileInfo,
        Name $originName,
        Name $newName,
        Differ $differ
    ) {
        $this->fileInfo = $fileInfo;
        $this->originName = $originName;
        $this->newName = $newName;
        $this->differ = $differ;
    }

    public function process()
    {
        /** @var MutableString $code */
        $code = \NamaeSpace\traverseToReplace($this->fileInfo, $this->originName, $this->newName);

        $diff = null;
        if ($code->hasModification()) {
            $diff = "<info>{$this->fileInfo->getFilename()}</info>\n" .
                $this->differ->diff($code->getOrigin(), $code->getModified()) . "\n";
        }

        return ['.', $diff];
    }

    /**
     * @param Messenger $messenger
     * @param LoopInterface $loop
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('return', function (Payload $payload) {
            try {
                $fileInfo = new SplFileInfo($payload['target_real_path']);
                $originName = new Name($payload['origin_name']);
                $newName = new Name($payload['new_name']);
                $differ = new Differ("--- Original\n+++ New\n", false);
                list($stdout, $stdoutPool) = (new self($fileInfo, $originName, $newName, $differ))->process();

                return \React\Promise\resolve(['stdout' => $stdout, 'stdout_pool' => $stdoutPool]);
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
