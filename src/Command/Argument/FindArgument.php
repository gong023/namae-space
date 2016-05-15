<?php

namespace NamaeSpace\Command\Argument;

use NamaeSpace\Command\Argument;
use TurmericSpice\ReadWriteAttributes;

class FindArgument extends Argument
{
    use ReadWriteAttributes {
        mustHaveAsString as public getFindPath;
        mustHaveAsString as public getFindNameSpace;
        mustHaveAsString as public getExcludePath;
    }
}
