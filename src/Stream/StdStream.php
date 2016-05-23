<?php

namespace NamaeSpace\Stream;

use SebastianBergmann\Diff\Differ;

class StdStream
{
    const OUTPUT = 'php://output';

    /**
     * @var Differ
     */
    private $differ;

    public function __construct(Differ $differ)
    {
        $this->differ = $differ;
    }

    public function put($data)
    {
        return file_put_contents(static::OUTPUT, $data);
    }

    public function putDiff($from, $to)
    {
        if ($from === $to) {
            return;
        }
        $this->put($this->differ->diff($from, $to));
    }
}
