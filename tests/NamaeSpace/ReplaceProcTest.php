<?php

namespace NamaeSpace;

use NamaeSpace\Visitor\ReplaceVisitor;
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
        list($expected, $origin) = loadFixture($file);
        $replaceProc = ReplaceProc::create(new Name($originName), new Name($newName));
        $code = $replaceProc->replace($origin);
        ReplaceVisitor::$targetClass = false;

        $this->assertSame($origin, $code->getOrigin(), $file);
        $this->assertSame($expected, $code->getModified(), $file);
    }

    public static function fixtureProvider()
    {
        return [
            ['ExprNew', 'Origin', 'Replaced'],
            ['ExprNewFullyQuallyfied', 'Test\\A\\Origin', 'Test\\B\\Replaced'],
            ['StmtUse', 'A\\B\\Origin', 'A\\B\\Replaced'],
            ['StmtGroupUse', 'Origin', 'Replaced'],
            ['ExprFuncCall', 'Origin', 'Replaced'],
            ['ExprInstanceof', 'Origin', 'Replaced'],
            ['ExprInstanceofFullyQuallyfied', 'Test\\Origin', 'Test\\Replaced'],
            ['StmtCatch', 'Origin', 'Replaced'],
            ['StmtClassMethod', 'Origin', 'Replaced'],
            ['StmtFunction', 'Origin', 'Replaced'],
            ['ExprClosure', 'Origin', 'Replaced'],
            ['TraitUse', 'Origin', 'Replaced'],
            ['ExprStaticCall', 'Origin', 'Replaced'],
            ['ExprStaticPropertyFetch', 'Origin', 'Replaced'],
            ['ExprClassConstFetch', 'Origin', 'Replaced'],
            ['AsAlias', 'A\\B\\Origin', 'A\\B\\Replaced'],
            ['StmtClassExtends', 'Origin', 'Replaced'],
            ['StmtClassImplements', 'Origin', 'Replaced'],
            ['StmtInterfaceExtends', 'Origin', 'Replaced'],
            ['StmtClassTarget', 'A\\B\\Origin', 'A\\XXX\\Replaced'],
            ['StmtClassNonTarget', 'A\\B\\Origin', 'A\\B\\Replaced'],
            ['StmtClassGlobal', 'Origin', 'A\\B\\Replaced'],
            ['StmtInterfaceTarget', 'A\\B\\Origin', 'A\\XXX\\Replaced'],
            ['StmtTraitTarget', 'A\\B\\Origin', 'A\\XXX\\Replaced'],
            ['Integration', 'A\\B\\Origin', 'A\\B\\Replaced'],
        ];
    }
}
