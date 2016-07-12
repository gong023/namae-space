<?php

namespace NamaeSpace;

class ComposerContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider psr0Provider
     * @param $autoload
     * @param $expected
     */
    public function testPsr0Dirs($autoload, $expected)
    {
        $content = new ComposerContent('basePath', new NullableArray($autoload));

        $this->assertSame($expected, $content->getPsr0Dirs());
    }

    public static function psr0Provider()
    {
        return [
            'plain' => [
                [
                    'autoload' => [
                        'psr-0' => ['Foo' => 'src/', 'Bar' => 'app/']
                    ],
                    'autoload-dev' => [
                        'psr-0' => ['Foo\\Tests' => 'tests/']
                    ],
                ],
                [
                    'basePath/src/', 'basePath/app/', 'basePath/tests/',
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'psr-0' => ['Foo' => 'src/', 'Bar' => 'app/']
                    ],
                ],
                [
                    'basePath/src/', 'basePath/app/',
                ]
            ],
            'multiple_dirs' => [
                [
                    'autoload' => [
                        'psr-0' => [ 'Foo' => ['src/', 'lib/'] ]
                    ],
                ],
                [
                    'basePath/src/', 'basePath/lib/',
                ]
            ],
            'duplicate_dirs' => [
                [
                    'autoload' => [
                        'psr-0' => [ 'A\\' => 'src/', 'B\\C\\' => 'src/', 'B_C_' => 'src/' ]
                    ],
                ],
                [
                    'basePath/src/'
                ]
            ],
            'undefined' => [
                [], [],
            ]
        ];
    }

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
                    'autoload-dev' => [
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

        $this->assertSame($expected, $content->getClassmapValues());
    }

    public static function classMapProvider()
    {
        return [
            'plain' => [
                [
                    'autoload' => [
                        'classmap' => ['src/', 'Something.php'],
                    ],
                    'autoload-dev' => [
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

        $this->assertSame($expected, $content->getFilesValues());
    }

    public static function filesProvider()
    {
        return [
            'plain' => [
                [
                    'autoload' => [
                        'files' => ['A.php', 'B.php'],
                    ],
                    'autoload-dev' => [
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

    /**
     * @dataProvider includePathProvider
     * @param $includePath
     * @param $expected
     */
    public function testIncludePathDirs($includePath, $expected)
    {
        $content = new ComposerContent('basePath', new NullableArray($includePath));

        $this->assertSame($expected, $content->getIncludePathDirs());
    }

    public static function includePathProvider()
    {
        return [
            'plain' => [
                [
                    'include-path' => ['lib/', 'src/'],
                ],
                [
                    'basePath/lib/', 'basePath/src/',
                ]
            ],
            'undefined' => [
                [], []
            ]
        ];
    }

    public function testGetPathAndDirs()
    {
        $content = new ComposerContent('basePath', new NullableArray([
            'autoload' => [
                'psr-4' => ['Bar' => 'app/'],
                'psr-0' => ['Foo' => ['src/', 'lib/']],
                'classmap' => ['Something.php'],
                'files' => ['A.php', 'B.php'],
            ],
            'autoload-dev' => [
                'psr-4' => ['Foo\\Tests' => 'tests/']
            ],
        ]));

        $expected = [
            'basePath/app/',
            'basePath/tests/',
            'basePath/src/',
            'basePath/lib/',
            'basePath/Something.php',
            'basePath/A.php',
            'basePath/B.php',
        ];

        $this->assertSame($expected, $content->getFileAndDirsToSearch());
    }
}
