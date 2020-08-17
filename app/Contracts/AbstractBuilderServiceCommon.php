<?php

namespace Laztopaz\Contracts;

abstract class AbstractBuilderServiceCommon
{
    /**
     * @return string
     */
    protected function getNewLine(): string
    {
        $systemOs = PHP_OS;

        if ($systemOs === 'Windows') {
            return static::PHP_CRT . PHP_EOL;
        }
        return PHP_EOL;
    }
}
