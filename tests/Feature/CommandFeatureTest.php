<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Laztopaz\Builders\ModelServiceBuilder;
use Laztopaz\Contracts\ConstantInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CommandFeatureTest extends TestCase implements ConstantInterface
{
    /**
     * @var MockObject
     */
    private $mockedModelBuilder;

    private $modelBuilder;
    private $modelPath;
    /**
     * @var string
     */
    private $migrationPath;
    /**
     * @var string
     */
    private $entityPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockedModelBuilder = $this->getMockBuilder(ModelServiceBuilder::class)->getMock();
        $this->modelBuilder = new ModelServiceBuilder;
        $this->modelPath = getcwd() . DIRECTORY_SEPARATOR . self::MODEL_FOLDER;
        $this->migrationPath = getcwd().DIRECTORY_SEPARATOR.self::DEFAULT_MIGRATION_FOLDER;
        $this->entityPath = getcwd() . DIRECTORY_SEPARATOR . 'app/Entities';
    }

    public function testForStringFields(): void
    {
        $this->artisan('make:crud Test1')
            ->expectsQuestion(static::ENTER_A_FIELD, 'firstname')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test1.php');
        $migrationFileName = $this->getMigrationFileName("create_Test1_table");
        self::assertFileExists( $this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testForStringWithEnumField(): void
    {
        $this->artisan('make:crud Test2')
            ->expectsQuestion(static::ENTER_A_FIELD, 'firstname')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'hobbies')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'enum')
            ->expectsQuestion(static::ENTER_ENUM_VALUES, 'soccer, hiking')
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test2.php');
        $migrationFileName = $this->getMigrationFileName("create_Test2_table");
        self::assertFileExists( $this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testThatUsingCustomIncrementDoesNotBreakTheApp(): void
    {
        $this->artisan('make:crud Test3')
            ->expectsQuestion(static::ENTER_A_FIELD, 'firstname')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'id')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'tinyIncrements')
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test3.php');
        $migrationFileName = $this->getMigrationFileName("create_Test3_table");
        self::assertFileExists( $this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testThatAddingTimestampDoesNotBreakTheApp(): void
    {
        $this->artisan('make:crud Test4')
            ->expectsQuestion(static::ENTER_A_FIELD, 'firstname')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'timestamp')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'timestampsTz')
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test4.php');
        $migrationFileName = $this->getMigrationFileName("create_Test4_table");
        self::assertFileExists( $this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testThatAddingUUIDDoesNotBreakTheApp(): void
    {
        $this->artisan('make:crud Test5')
            ->expectsQuestion(static::ENTER_A_FIELD, 'name')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'uuid')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'uuid')
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test5.php');
        $migrationFileName = $this->getMigrationFileName("create_Test5_table");
        self::assertFileExists( $this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testThatOnlyModelWasGenerated(): void
    {
        $this->artisan('make:crud Test6 --g=model')
            ->expectsQuestion(static::ENTER_A_FIELD, 'name')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test6.php');
        $migrationFileName = $this->getMigrationFileName("create_Test6_table");
        self::assertFileNotExists($this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testThatOnlyMigrationWasGenerated(): void
    {
        $this->artisan('make:crud Test7 --g=migration')
            ->expectsQuestion(static::ENTER_A_FIELD, 'name')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileNotExists($this->modelPath . DIRECTORY_SEPARATOR . 'Test7.php');
        $migrationFileName = $this->getMigrationFileName("create_Test7_table");
        self::assertFileExists($this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    public function testThatCustomFolderWasGeneratedForModel(): void
    {
        $this->artisan('make:crud Test8 --g=model --f=Entities')
            ->expectsQuestion(static::ENTER_A_FIELD, 'name')
            ->expectsQuestion(static::SELECT_FIELD_TYPE, 'string')
            ->expectsQuestion(static::ENTER_THE_LENGTH, '')
            ->expectsOutput(static::DEFAULT_LENGTH_USED)
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        self::assertFileExists($this->entityPath . DIRECTORY_SEPARATOR . 'Test8.php');
        $migrationFileName = $this->getMigrationFileName("create_Test8_table");
        self::assertFileNotExists($this->migrationPath . DIRECTORY_SEPARATOR . $migrationFileName);
    }

    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    public function tearDown(): void
    {
        File::deleteDirectory($this->modelPath);
        File::deleteDirectory($this->entityPath);
        File::deleteDirectory($this->migrationPath);

        parent::tearDown();
    }

    /**
     * @param string $name
     */
    protected function getMigrationFileName(string $name): string
    {
        return strtolower($this->getDatePrefix() . '_' . $name . static::FILE_EXTENSION);
    }

}
