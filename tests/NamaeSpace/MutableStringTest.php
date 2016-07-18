<?php

namespace NamaeSpace;

class MutableStringTest extends \PHPUnit_Framework_TestCase
{
    public function testModify()
    {
        $origin = <<<CODE
use A\B;

class A
{
    public function __construct(B \$b)
    {
        \$this->b = \$b
    }
}
CODE;
        $modified = <<<CODE
use A\XX;

class A
{
    public function __construct(XX \$b)
    {
        \$this->b = \$b
    }
}
CODE;
        $mutableString = new MutableString($origin);
        $mutableString->addModification(6, 'B', 'XX');
        $mutableString->addModification(52, 'B', 'XX');

        $this->assertSame($modified, $mutableString->getModified());
        $this->assertSame($origin, $mutableString->getOrigin());
    }
}
