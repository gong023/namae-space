<?php

namespace NamaeSpace;

use PhpParser\Node\Name;

class ReplaceProcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReplaceProc
     */
    private static $replaceProc;

    public static function setUpBeforeClass()
    {
        $originName = new Name('Origin');
        $newName = new Name('Replaced');
        self::$replaceProc = ReplaceProc::create($originName, $newName);
    }

    public function testExprNew()
    {
        list($expected, $target) = loadFixture('ExprNew');
        $code = self::$replaceProc->replace($target);

        $this->assertSame($target, $code->getOrigin());
        $this->assertSame($expected, $code->getModified());
    }
}
