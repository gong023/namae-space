<?php

namespace NamaeSpace\Command\Argument;

use NamaeSpace\Command\Argument;
use TurmericSpice\ReadWriteAttributes;

class FindArgument extends Argument
{
    use ReadWriteAttributes {
        mustHaveAsString as public getFindPath;
        mayHaveAsString  as public getExcludePath;
        mustHaveAsString as public getAutoloadBasePath;
        mustHaveAsString as public getBeforeNameSpace;
        mustHaveAsString as public getAfterNameSpace;
    }
}
