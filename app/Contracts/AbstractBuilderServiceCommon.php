<?php

namespace App\Contracts;

abstract class AbstractBuilderServiceCommon
{
    /**
     * @return string
     */
    protected function getNewLine(): string
    {
        return PHP_EOL;
    }
}
