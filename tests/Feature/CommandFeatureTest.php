<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommandFeatureTest extends TestCase
{
    public function testConsoleCommand(): void
    {
        $this->artisan('make:crud User')
        ->expectsQuestion('Enter field name', 'firstname')
        ->expectsQuestion('Select field type', 'string')
        ->expectsQuestion('Enter the length', '')
        ->expectsOutput('Default length will be used instead');
    }

}
