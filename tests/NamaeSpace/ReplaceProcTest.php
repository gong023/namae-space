<?php

namespace NamaeSpace;

use PhpParser\Node\Name;

class ReplaceProcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider fixtureProvider
     * @param $file
     * @param $originName
     * @param $newName
     */
    public function testReplaceByFixture($file, $originName, $newName)
    {
        list($expected, $target) = loadFixture($file);
        $replaceProc = ReplaceProc::create(new Name($originName), new Name($newName));
        $code = $replaceProc->replace($target);

        $this->assertSame($target, $code->getOrigin(), $file);
        $this->assertSame($expected, $code->getModified(), $file);
    }

    public static function fixtureProvider()
    {
        return [
            ['ExprNew', 'Origin', 'Replaced'],
        ];
    }

    public function testReplaceFullyQualified()
    {
        list($expected, $target) = loadFixture('ExprNewFullyQuallyfied');
        $replaceProc = ReplaceProc::create(new Name('Test\\A\\Origin'), new Name('Test\\B\\Replaced'));
        $code = $replaceProc->replace($target);

        $this->assertSame($target, $code->getOrigin());
        $this->assertSame($expected, $code->getModified());
    }
}
