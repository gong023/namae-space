<?php

namespace NamaeSpace;

use PhpParser\Node\Name;

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
                    'src/', 'app/', 'tests/',
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'psr-0' => ['Foo' => 'src/', 'Bar' => 'app/']
                    ],
                ],
                [
                    'src/', 'app/',
                ]
            ],
            'multiple_dirs' => [
                [
                    'autoload' => [
                        'psr-0' => [ 'Foo' => ['src/', 'lib/'] ]
                    ],
                ],
                [
                    'src/', 'lib/',
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
                    'src/', 'app/', 'tests/',
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'psr-4' => ['Foo' => 'src/', 'Bar' => 'app/']
                    ],
                ],
                [
                    'src/', 'app/',
                ]
            ],
            'multiple_dirs' => [
                [
                    'autoload' => [
                        'psr-4' => [ 'Foo' => ['src/', 'lib/'] ]
                    ],
                ],
                [
                    'src/', 'lib/',
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
                    'src/', 'Something.php', 'C.php'
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'classmap' => ['src/', 'Something.php'],
                    ],
                ],
                [
                    'src/', 'Something.php',
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
                    'A.php', 'B.php', 'C.php'
                ]
            ],
            'without_autoload_dev' => [
                [
                    'autoload' => [
                        'files' => ['A.php', 'B.php'],
                    ],
                ],
                [
                    'A.php', 'B.php',
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
                    'lib/', 'src/',
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
                'psr-0' => [ 'A\\' => 'src/', 'B\\C\\' => 'src/', 'B_C_' => 'src/' ], // duplicate dir
                'classmap' => ['Something.php'],
                'files' => ['A.php', 'B.php'],
            ],
            'autoload-dev' => [
                'psr-4' => ['Foo\\Tests' => 'tests/']
            ],
        ]));

        $expected = [
            'app/',
            'tests/',
            'src/',
            'Something.php',
            'A.php',
            'B.php',
        ];

        $this->assertSame($expected, array_values($content->getFileAndDirsToSearch()));
    }

    /**
     * @dataProvider replaceDirProvider
     * @param $replacedName
     * @param $content
     * @param $expected
     */
    public function testGetDirsToReplace($replacedName, $content, $expected)
    {
        $content = new ComposerContent('basePath', new NullableArray($content));
        $dirs = $content->getDirsToReplace(new Name($replacedName));

        $this->assertSame($expected, $dirs);
    }

    public static function replaceDirProvider()
    {
        return [
            'plain' => [
                'A\\B',
                [
                    'autoload' => [
                        'psr-4' => [
                            'A\\'  => 'src/',
                        ],
                    ],
                ],
                [
                    'src/',
                ]
            ],
            'longName' => [
                'A\\B\\C\\D\\E\\F',
                [
                    'autoload' => [
                        'psr-4' => [
                            'A\\'  => 'src/',
                        ],
                    ],
                ],
                [
                    'src/B/C/D/E/',
                ]

            ],
            'mappedLongKey' => [
                'A\\B\\C',
                [
                    'autoload' => [
                        'psr-4' => [
                            'A\\B\\' => 'app/',
                            'A\\'  => 'src/',
                        ],
                    ],
                    'autoload-dev' => [
                        'psr-4' => [
                            'A\\B\\' => 'tests/',
                        ]
                    ],
                ],
                [
                    'app/B/', 'tests/B/'
                ]
            ],
            'hasMultipleDir' => [
                'A\\B',
                [
                    'autoload' => [
                        'psr-4' => [
                            'A\\'  => ['src/', 'lib/'],
                        ],
                    ],
                ],
                [
                    'src/', 'lib/',
                ]
            ],
            'underscoreSplit' => [
                'A\\B\\C',
                [
                    'autoload' => [
                        'psr-0' => [
                            'A_B_'  => 'src/',
                        ],
                    ],
                ],
                [
                    'src/B/',
                ]
            ],
            'notFound' => [
                'X\\Y',
                [
                    'autoload' => [
                        'psr-4' => [
                            'A\\'  => 'src/',
                        ],
                    ],
                ],
                []
            ],
            'undefined' => [
                '', [], []
            ]
        ];
    }
}
