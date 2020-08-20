<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Laztopaz\Builders\ModelServiceBuilder;
use Laztopaz\Contracts\ConstantInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CommandFeatureTest extends TestCase implements ConstantInterface
{
    public const ENTER_A_FIELD = 'Enter field name';
    public const SELECT_FIELD_TYPE = 'Select field type';
    public const ENTER_THE_LENGTH = 'Enter the length';
    public const DEFAULT_LENGTH_USED = 'Default length will be used instead';
    public const DO_YOU_WANT_TO_EXIT = 'Are you sure you want to exit?';
    public const MODEL_FOLDER = 'app/Models';
    /**
     * @var MockObject
     */
    private $mockedModelBuilder;

    private $modelBuilder;
    private $modelPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockedModelBuilder = $this->getMockBuilder(ModelServiceBuilder::class)->getMock();
        $this->modelBuilder = new ModelServiceBuilder;
        $this->modelPath = getcwd().DIRECTORY_SEPARATOR.self::MODEL_FOLDER;
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

        $this->assertFileExists($this->modelPath.DIRECTORY_SEPARATOR.'Test1.php');
        // Expect 2 files to be generated one for the model and the other for the

        //dd($this->modelBuilder->getMigrations());
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
            ->expectsQuestion('Enter the ENUM values separated by a comma', 'soccer, hiking')
            ->expectsQuestion(static::ENTER_A_FIELD, 'exit')
            ->expectsConfirmation(static::DO_YOU_WANT_TO_EXIT, 'yes');

        $this->assertFileExists($this->modelPath.DIRECTORY_SEPARATOR.'Test2.php');
    }

    public function tearDown(): void
    {
        File::deleteDirectory(getcwd().DIRECTORY_SEPARATOR.self::MODEL_FOLDER);
        File::deleteDirectory(getcwd().DIRECTORY_SEPARATOR.self::DEFAULT_MIGRATION_FOLDER);

        parent::tearDown();
    }

}
