<?php

namespace NamaeSpace\Command\Context;

use NamaeSpace\Command\Context;

class FindContext extends Context
{
    private $findName;

    /**
     * @return array
     */
    public function getPayload()
    {
        return [
            'find_name'        => $this->findName,
            'target_real_path' => $this->targetRealPath,
        ];
    }

    public function setFindNameFromInput()
    {
        $this->findName = $this->normalizeNameSpace($this->requiredInput('find_namespace'));
        return $this;
    }
}