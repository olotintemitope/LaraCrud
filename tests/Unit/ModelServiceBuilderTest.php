<?php

namespace Tests\Unit;

use Laztopaz\Builders\ModelServiceBuilder;
use Laztopaz\Contracts\ConstantInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ModelServiceBuilderTest extends TestCase implements ConstantInterface
{
    /**
     * @var MockObject
     */
    private $mockedModelBuilder;

    private $modelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockedModelBuilder = $this->getMockBuilder(ModelServiceBuilder::class)->getMock();
        $this->modelBuilder = new ModelServiceBuilder;
    }

    public function testMigrationsData(): void
    {
        $migrations = $this->getMigrationWithNames();

        $this->mockedModelBuilder->expects($this->once())
            ->method('setMigrations')
            ->with($migrations)
            ->willReturn($this->modelBuilder);
        $this->mockedModelBuilder->setMigrations($migrations);

        $this->mockedModelBuilder->expects($this->once())
            ->method('getTraits')
            ->willReturn('use SoftDeletes');
        $result = $this->mockedModelBuilder->getTraits();

        dd($result);

        $this->assertEquals($migrations, $result);
    }

    public function testThatModelHasFieldCasts(): void
    {
        $migrations = $this->getMigrationWithDate();

        $this->mockedModelBuilder
            ->expects($this->once())
            ->method('setMigrations')
            ->with($migrations)
            ->willReturn($this->mockedModelBuilder);

        $this->mockedModelBuilder->setMigrations($migrations);

        try {
            $this->modelBuilder->setMigrations($migrations);
            $castFields = $this->invokeMethod($this->modelBuilder, 'getCastsField', $migrations);
            $this->assertEquals(self::PHP_TAB.self::PHP_TAB."'dob' => 'date'", $castFields);
        } catch (\ReflectionException $e) {
        }
    }

    public function testThatModelTheCorrectFillableFields(): void
    {
        $migrations = $this->getMigrationWithDate();

        $this->mockedModelBuilder
            ->expects($this->once())
            ->method('setMigrations')
            ->with($migrations)
            ->willReturn($this->mockedModelBuilder);

        $this->mockedModelBuilder->setMigrations($migrations);

        try {
            $this->modelBuilder->setMigrations($migrations);
            $fillables = $this->invokeMethod($this->modelBuilder, 'getFields', $migrations);
            $this->assertStringContainsStringIgnoringCase('firstname', $fillables);
            $this->assertStringContainsStringIgnoringCase('lastname', $fillables);
            $this->assertStringContainsStringIgnoringCase('dob', $fillables);
        } catch (\ReflectionException $e) {
        }
    }

    public function testModelNameAndClassDefinitions($table = '$table'): void
    {
        $this->modelBuilder->setModelName('User');

        $this->assertEquals('User', $this->modelBuilder->getModelName());
        $this->assertStringContainsString('class User extends Model', $this->modelBuilder->getClassDefinition());

        $this->modelBuilder->setNameSpace('App\User');
        $this->assertStringContainsString('namespace App\User', $this->modelBuilder->getNameSpace());
        $this->assertStringContainsString("protected $table = 'users';", $this->modelBuilder->getModelTableDefinition());
    }

    /**
     * @return array
     */
    protected function getMigrationWithDate(): array
    {
        return [
            'firstname' => ['field_type' => 'string', 'length' => 0],
            'lastname' => ['field_type' => 'string', 'length' => 0],
            'dob' => ['field_type' => 'date'],
        ];
    }

    /**
     * @return array[]
     */
    protected function getMigrationWithNames(): array
    {
        return [
            'firstname' => ['field_type' => 'string', 'length' => 0],
            'lastname' => ['field_type' => 'string', 'length' => 0],
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
