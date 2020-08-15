<?php

namespace App\Contracts;

abstract class AbstractBuilderServiceConstants
{
    /**
     * @return string
     */
    protected function getNewLine(): string
    {
        return PHP_EOL;
    }
}
