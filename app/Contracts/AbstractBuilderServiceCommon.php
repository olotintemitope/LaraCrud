<?php

namespace Laztopaz\Contracts;

use ICanBoogie\Inflector;

abstract class AbstractBuilderServiceCommon
{
    /**
     * @var Inflector
     */
    protected Inflector $inflector;

    public function __construct()
    {
        $this->inflector = Inflector::get('en');
    }

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
