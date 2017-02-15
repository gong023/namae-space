<?php

namespace NamaeSpace;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    private static $iterTest;

    public static function setUpBeforeClass()
    {
        self::$iterTest = __DIR__ . '/../fixtures/iterTest/';
    }

    public function testGetIterator()
    {
        // get all
        $files = iterator_to_array(getIterator(self::$iterTest));
        $this->assertCount(3, $files);
        foreach (['A.php', 'B.php', 'C.php'] as $expected) {
            /** @var \SplFileInfo */
            $file = array_shift($files);
            $this->assertSame($expected, $file->getFileName());
        }

        // get a file
        /** @var $files \SplFileInfo[] */
        $files = getIterator(self::$iterTest . 'B/C/C.php');
        $this->assertCount(1, $files);
        $this->assertSame('C.php', $files[0]->getFileName());

        // exclude a file
        $files = iterator_to_array(getIterator(self::$iterTest, ['B.php']));
        $this->assertCount(2, $files);
        foreach (['A.php', 'C.php'] as $expected) {
            /** @var \SplFileInfo */
            $file = array_shift($files);
            $this->assertSame($expected, $file->getFileName());
        }

        // exclude dir
        $files = iterator_to_array(getIterator(self::$iterTest, ['B']));
        $this->assertCount(1, $files);
        foreach (['A.php'] as $expected) {
            /** @var \SplFileInfo */
            $file = array_shift($files);
            $this->assertSame($expected, $file->getFileName());
        }
    }
}