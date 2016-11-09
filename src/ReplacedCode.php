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

    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    public function __construct($string, SplFileInfo $fileInfo)
    {
        parent::__construct($string);
        $this->fileInfo = $fileInfo;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

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

    public static function create($filePath)
    {
        $info = new SplFileInfo($filePath);
        $code = file_get_contents($info->getRealPath());

        return new self($code, $info);
    }
}