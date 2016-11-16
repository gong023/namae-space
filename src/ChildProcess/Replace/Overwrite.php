<?php

namespace NamaeSpace\ChildProcess\Replace;

use PhpParser\Node\Name;
use React\EventLoop\LoopInterface;
use SplFileInfo;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class Overwrite implements  ChildInterface
{
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

    private $fileDir;

    public function __construct(
        SplFileInfo $fileInfo,
        Name $originName,
        Name $newName,
        $fileDir
    ) {
        $this->fileInfo = $fileInfo;
        $this->originName = $originName;
        $this->newName = $newName;
        $this->fileDir = $fileDir;
    }

    public function process()
    {
        /** @var \NamaeSpace\ReplacedCode $code */
        $code = \NamaeSpace\traverseToReplace($this->fileInfo, $this->originName, $this->newName);

        if ($code->isTargetClass) {
            $outputFilePath = "{$this->fileDir}/{$this->newName->getLast()}.php";
            @mkdir($this->fileDir, 0755, true);
            file_put_contents($outputFilePath, $code->getModified());
            @unlink($this->fileInfo->getRealPath());
            @rmdir($this->fileInfo->getPath()); // $fileInfo->getPath()
        } else {
            file_put_contents($this->fileInfo->getRealPath(), $code->getModified());
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
                $fileInfo = new SplFileInfo($payload['target_real_path']);
                $originName = new Name($payload['origin_name']);
                $newName = new Name($payload['new_name']);
                $fileDir = $payload['project_dir'] . '/' . $payload['replace_dir'];

                (new self($fileInfo, $originName, $newName, $fileDir))->process();
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