<?php

namespace Tests\Unit;

use Laztopaz\Builders\MigrationServiceBuilder;
use Laztopaz\Builders\ModelServiceBuilder;
use Mockery;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class MigrationServiceBuilderTest extends TestCase
{
    public const USE_SOFT_DELETES = 'use SoftDeletes';
    /**
     * @var MigrationServiceBuilder
     */
    private $migrationBuilder;
    /**
     * @var MockObject
     */
    private $mockedMigrationBuilder;
    /**
     * @var MockBuilder
     */
    private $mockedModelBuilder;
    /**
     * @var ModelServiceBuilder
     */
    private $modelBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedModelBuilder = $this->getMockBuilder(ModelServiceBuilder::class)->getMock();
        $this->mockedMigrationBuilder = Mockery::mock(MigrationServiceBuilder::class, [new ModelServiceBuilder]);
        $this->modelBuilder = new ModelServiceBuilder;
        $this->migrationBuilder = new MigrationServiceBuilder($this->modelBuilder);
    }

    public function testThatMigrationHasSoftDelete(): void
    {
        $migrations = $this->getMigrationWithSoftDeletes();
        $this->mockedModelBuilder->expects($this->once())
            ->method('setMigrations')
            ->with($migrations)
            ->willReturn($this->modelBuilder);
        $this->mockedModelBuilder->setMigrations($migrations);

        $this->mockedMigrationBuilder
            ->shouldReceive('setTraits')
            ->once()
            ->withSomeOfArgs([self::USE_SOFT_DELETES])
            ->andReturn($this->migrationBuilder);

        $this->mockedMigrationBuilder
            ->setTraits([self::USE_SOFT_DELETES]);

        $this->mockedMigrationBuilder
            ->shouldReceive('getTraits')
            ->once()
            ->andReturn(self::USE_SOFT_DELETES);

        $result = $this->mockedMigrationBuilder->getTraits();
        $this->assertEquals(self::USE_SOFT_DELETES, $result);
    }

    /**
     * @return array
     */
    protected function getMigrationWithSoftDeletes(): array
    {
        return [
            'firstname' => ['field_type' => 'string', 'length' => 0],
            'lastname' => ['field_type' => 'string', 'length' => 0],
            'dob' => ['field_type' => 'date'],
            'softDeletes' => ['field_type' => 'softDeletes'],
        ];
    }



    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
