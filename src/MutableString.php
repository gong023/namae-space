<?php

namespace NamaeSpace;

class MutableString
{
    private $string;
    // [[pos, removed, inserted]]
    private $modifications = [];

    /**
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    public function getOrigin()
    {
        return $this->string;
    }

    /**
     * @param int $pos
     * @param $inserted
     * @param $removed
     * @return $this
     */
    public function addModification($pos, $removed, $inserted)
    {
        $this->modifications[] = [$pos, $removed, $inserted];

        return $this;
    }

    public function getModified()
    {
        // Sort by position
        usort($this->modifications, function($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return $a[0] < $b[0] ? -1 : 1;
        });

        $result = '';
        $startPos = 0;
        foreach ($this->modifications as list($pos, $removed, $inserted)) {
            $result .= substr($this->string, $startPos, $pos - $startPos);
            $result .= $inserted;
            $startPos = $pos + strlen($removed);
        }
        $result .= substr($this->string, $startPos);

        return $result;
    }
}
