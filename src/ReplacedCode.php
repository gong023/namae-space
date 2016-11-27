<?php

namespace NamaeSpace;

use SplFileInfo;

class ReplacedCode extends MutableString
{
    public $isTargetClass = false;
    // ugly properties to add use stmt
    public $named = false;
    public $stmtUseModified = false;
    public $stmtNameSpacePosEnd;
    public $stmtClassLikePosStart;
    public $stmtUsesPosStart;

    public function getPosToAddUseStmt()
    {
        if ($this->stmtNameSpacePosEnd) {
            return $this->stmtNameSpacePosEnd;
        } elseif ($this->stmtClassLikePosStart) {
            return $this->stmtClassLikePosStart;
        } elseif ($this->stmtUsesPosStart) {
            return $this->stmtUsesPosStart;
        }

        return strlen("<?php\n");
    }

    public static function create(SplFileInfo $fileInfo)
    {
        $code = file_get_contents($fileInfo->getRealPath());

        return new self($code);
    }
}