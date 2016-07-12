<?php

namespace NamaeSpace;

class ComposerContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider psr4Provider
     * @param $autoload
     * @param $expected
     */
    public function testPsr4Dirs($autoload, $expected)
    {
        $content = new ComposerContent('basePath', new NullableArray($autoload));

        $this->assertSame($expected, $content->getPsr4Dirs());
    }

    public static function psr4Provider()
    {
        return [
            'plain' => [
                [
                    'autoload' => [
                        'psr-4' => ['Foo' => 'src/', 'Bar' => 'app/']
                    ],
                    'autoload_dev' => [
                        'psr-4' => ['Foo\\Tests' => 'tests/']
                    ],
                ],
                [
                    'basePath/src/', 'basePath/app/', 'basePath/tests/',
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'psr-4' => ['Foo' => 'src/', 'Bar' => 'app/']
                    ],
                ],
                [
                    'basePath/src/', 'basePath/app/',
                ]
            ],
            'multiple_dirs' => [
                [
                    'autoload' => [
                        'psr-4' => [ 'Foo' => ['src/', 'lib/'] ]
                    ],
                ],
                [
                    'basePath/src/', 'basePath/lib/',
                ]
            ],
            'undefined' => [
                [], [],
            ]
        ];
    }

    /**
     * @dataProvider classMapProvider
     * @param $autoload
     * @param $expected
     */
    public function testClassMap($autoload, $expected)
    {
        $content = new ComposerContent('basePath', new NullableArray($autoload));

        $this->assertSame($expected, $content->getClassmapDirs());
    }

    public static function classMapProvider()
    {
        return [
            'plain' => [
                [
                    'autoload' => [
                        'classmap' => ['src/', 'Something.php'],
                    ],
                    'autoload_dev' => [
                        'classmap' => ['C.php'],
                    ],
                ],
                [
                    'basePath/src/', 'basePath/Something.php', 'basePath/C.php'
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'classmap' => ['src/', 'Something.php'],
                    ],
                ],
                [
                    'basePath/src/', 'basePath/Something.php',
                ]
            ],
            'undefined' => [
                [], [],
            ]
        ];
    }

    /**
     * @dataProvider filesProvider
     * @param $autoload
     * @param $expected
     */
    public function testFiles($autoload, $expected)
    {
        $content = new ComposerContent('basePath', new NullableArray($autoload));

        $this->assertSame($expected, $content->getFiles());
    }

    public static function filesProvider()
    {
        return [
            'plain' => [
                [
                    'autoload' => [
                        'files' => ['A.php', 'B.php'],
                    ],
                    'autoload_dev' => [
                        'files' => ['C.php'],
                    ],
                ],
                [
                    'basePath/A.php', 'basePath/B.php', 'basePath/C.php'
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'files' => ['A.php', 'B.php'],
                    ],
                ],
                [
                    'basePath/A.php', 'basePath/B.php',
                ]
            ],
            'undefined' => [
                [], []
            ]
        ];
    }
}
