<?php

namespace NamaeSpace\Command\Argument;

use NamaeSpace\Command\Argument;
use TurmericSpice\ReadWriteAttributes;

class ReplaceArgument extends Argument
{
    use ReadWriteAttributes {
        mustHaveAsString as public getFindPath;
        mustHaveAsString as public getExcludePath;
        mustHaveAsString as public getAutoloadBasePath;
        mustHaveAsString as public getBeforeNameSpace;
        mustHaveAsString as public getAfterNameSpace;
        mayHaveAsBoolean as public isDryRun;
    }
}
