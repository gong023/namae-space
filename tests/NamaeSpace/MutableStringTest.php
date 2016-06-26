<?php

namespace NamaeSpace;

class MutableStringTest extends \PHPUnit_Framework_TestCase
{
    public function testModify()
    {
        $before = <<<CODE
use A\B;

class A
{
    public function __construct(B \$b)
    {
        \$this->b = \$b
    }
}
CODE;
        $expected = <<<CODE
use A\XX;

class A
{
    public function __construct(XX \$b)
    {
        \$this->b = \$b
    }
}
CODE;
        $mutableString = new MutableString($before);
        $mutableString->addModification(6, 'B', 'XX');
        $mutableString->addModification(52, 'B', 'XX');

        $this->assertSame($expected, $mutableString->getModified());
        $this->assertSame($before, $mutableString->getOrigin());
    }
}
