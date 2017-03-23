<?php

namespace NamaeSpace;

use PhpParser\Node\Name;

class TraverseToReplaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider fixtureProvider
     * @param $file
     * @param $originName
     * @param $newName
     */
    public function testReplaceByFixture($file, $originName, $newName)
    {
        $fileInfo = new \SplFileInfo(realpath(__DIR__ . '/../fixtures/origin/' . $file . '.php'));
        $originString = file_get_contents($fileInfo->getRealPath());
        $expectedString = file_get_contents(__DIR__ . '/../fixtures/replaced/' . $file . '.php');
        /** @var \NamaeSpace\ReplacedCode $code */
        $code = \NamaeSpace\traverseToReplace($fileInfo, new Name($originName), new Name($newName));

        $this->assertSame($originString, $code->getOrigin(), $file);
        $this->assertSame($expectedString, $code->getModified(), $file);
    }

    public static function fixtureProvider()
    {
        return [
            ['AddNamespaceToGlobal1', 'Target', 'A\\B\\Target'],
            ['AddNamespaceToGlobal2', 'Target', 'A\\B\\Target'],
            ['AddNamespaceToGlobal3', 'Target', 'A\\B\\Target'],
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
            ['AddStmtUseAfterNamespace', 'Origin', 'A\\B\\Replaced'],
            ['AddStmtUseBeforeClass', 'Origin', 'A\\B\\Replaced'],
            ['AddStmtUseAtFileStart', 'Origin', 'A\\B\\Replaced'],
            ['AddStmtUseBetweenName', 'Origin', 'A\\B\\Replaced'],
            ['Integration', 'A\\B\\Origin', 'A\\B\\Replaced'],
        ];
    }
}
