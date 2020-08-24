<?php

namespace Tests\Unit;

use Laztopaz\Builders\MigrationServiceBuilder;
use Laztopaz\Builders\ModelServiceBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class MigrationServiceBuilderTest extends TestCase
{
    /**
     * @var MigrationServiceBuilder
     */
    private $migrationBuilder;
    /**
     * @var MockObject
     */
    private $mockedMigrationBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        //$this->mockedModelBuilder = $this->getMockBuilder(ModelServiceBuilder::class);
        $this->mockedMigrationBuilder = \Mockery::mock(MigrationServiceBuilder::class, [new ModelServiceBuilder]);
            //->disableOriginalConstructor()
            //->getMock();
        $this->migrationBuilder = new MigrationServiceBuilder;
    }

    public function testThatMigrationHasSoftDelete(): void
    {
        //dd($this->mockedMigrationBuilder);
    }



    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
