<?php

namespace NamaeSpace;

class ComposerContentTest extends \PHPUnit_Framework_TestCase
{
    public function testPsr4Dirs()
    {
        $content = new ComposerContent('basePath', new NullableArray([
            'autoload' => [
                'psr-4' => [
                    'Monolog' => 'src/',
                ]
            ],
        ]));
        
        $this->assertSame(['basePath/src/'], $content->getPsr4Dirs());
    }
}
