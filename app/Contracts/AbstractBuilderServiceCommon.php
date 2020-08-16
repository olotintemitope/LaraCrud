<?php

namespace Laztopaz\Laracrud\Contracts;

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
