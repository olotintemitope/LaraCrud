<?php

namespace App\Commands;

use App\Contracts\FileWriterAbstractFactory;
use App\Services\FileWriterDirector;
use App\Services\MigrationFileWriter;
use App\Services\MigrationServiceBuilder;
use App\Services\ModelFileWriter;
use App\Services\ModelServiceBuilder;
use App\Services\OutPutDirector;
use App\Contracts\ConstantInterface;
use LaravelZero\Framework\Commands\Command;

class LaraCrudCommand extends Command implements ConstantInterface
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:crud {name : The name of the model (required)} {--folder= : The path to the model folder (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a crud operation out of your model';

    /**
     * Execute the console command.
     *
     * @param ModelFileWriter $modelFileWriter
     * @param MigrationFileWriter $migrationFileWriter
     * @return mixed
     */
    public function handle(ModelFileWriter $modelFileWriter, MigrationFileWriter $migrationFileWriter)
    {
        try {
            [$modelName, $defaultModelDirectory, $modelPath, $migrations] = $this->inputReader();
            $modelNamespace = str_replace('/', '\\', $defaultModelDirectory);

            if (!empty($modelName)) {
                $modelBuilder = $this->getModelBuilder($modelName, $migrations, $modelNamespace);
                $fileOutputDirector = new OutPutDirector($modelBuilder);
                $fileWriter = new FileWriterDirector($modelFileWriter);
                // Write to molder folder
                $fileWriter::write($defaultModelDirectory, $modelPath, $fileOutputDirector->getFileContent());
                $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");

                $migrationBuilder = $this->getMigrationBuilder($modelBuilder);
                $fileOutputDirector = new OutPutDirector($migrationBuilder);
                $fileWriter = new FileWriterDirector($migrationFileWriter);

                // Write to migration folder
                $fileWriter->setFileName("create_{$modelName}_table");
                [$migrationFulPath, $filePath] = $this->getMigrationDirectory($fileWriter);
                $fileWriter::write($migrationFulPath, $filePath, $fileOutputDirector->getFileContent());
                $this->info("{$modelName} migrations was generated for you and copied to the {$migrationFulPath} folder");
            }
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @return string
     */
    private function userWillSelectColumnFieldType(): string
    {
        return $this->choice(
            'Select field type',
            array_keys(static::AVAILABLE_COLUMN_TYPES),
            $defaultIndex = 38,
            $maxAttempts = null,
            $allowMultipleSelections = false
        );
    }

    /**
     * @return mixed|string|string[]
     */
    private function userWillEnterFieldName(): string
    {
        return str_replace(' ', '_', $this->ask('Enter field name'));
    }

    /**
     * @return mixed|string|string[]
     */
    private function askForFieldName(): string
    {
        return $this->userWillEnterFieldName();
    }

    /**
     * @param $migrations
     * @param $dbFieldName
     * @param $dbColumnFieldType
     * @return mixed
     */
    public function setMigrations(array $migrations, string $dbFieldName, string $dbColumnFieldType): array
    {
        // Cater for enum types. $table->enum('level', ['easy', 'hard']);
        if ('enum' === $dbColumnFieldType) {
            $enumValues = str_replace(" ", '', trim($this->ask('Enter enum values separated by a comma')));
            if (empty($enumValues)) {
                $this->error("field name is missing");
            }
            if (!empty($enumValues)) {
                $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'values' => explode(',', $enumValues)];
            }
        }

        if ('enum' !== $dbColumnFieldType) {
            $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType];
        }

        if (str_contains($dbColumnFieldType, 'string') || str_contains($dbColumnFieldType, 'integer')) {
            $fieldLength = (int)trim($this->ask('Enter the length'));
            $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'length' => $fieldLength];

            if (empty($fieldLength)) {
                $this->info("Default length will be used instead");
            }
        }

        return $migrations;
    }

    /**
     * @param string $modelName
     * @return array
     */
    protected function getModelDirectoryInfo(string $modelName): array
    {
        $modelDirectory = $this->option('folder');
        $applicationNamespace = ucwords(explode("\\", static::class)[0]);
        $defaultModelDirectory = FileWriterAbstractFactory::getDefaultDirectory($modelDirectory, $applicationNamespace);
        $modelPath = FileWriterAbstractFactory::getWorkingDirectory($defaultModelDirectory, $modelName);
        return array($defaultModelDirectory, $modelPath);
    }

    /**
     * @param string $modelName
     * @param array $migrations
     * @param string $modelNamespace
     */
    protected function getModelBuilder(string $modelName, array $migrations, string $modelNamespace): ModelServiceBuilder
    {
        return (new ModelServiceBuilder)
            ->setModelName($modelName)
            ->setMigrations($migrations)
            ->setNameSpace($modelNamespace)
            ->setModelDependencies([
                'use Illuminate\Database\Eloquent\Model',
            ]);
    }

    /**
     * @return array
     */
    protected function inputReader(): array
    {
        $migrations = ['id' => ['field_type' => 'increments'],];
        $modelName = strip_tags($this->argument('name'));

        if (empty($modelName)) {
            $this->error("Name argument is missing");
        }

        [$defaultModelDirectory, $modelPath] = $this->getModelDirectoryInfo($modelName);

        if (FileWriterAbstractFactory::fileExists($modelPath)) {
            $this->error("{$modelPath} already exist");
            exit();
        }

        do {
            $dbFieldName = $this->askForFieldName();
            if (!empty($dbFieldName) && 'exit' !== $dbFieldName && 'no' !== $dbFieldName) {
                $dbColumnFieldType = $this->userWillSelectColumnFieldType();
                $migrations = $this->setMigrations($migrations, $dbFieldName, $dbColumnFieldType);
            }
            if ('exit' === $dbFieldName) {
                if ($this->confirm('Are you sure you want to exit?', self::EXIT_CONSOLE)) {
                    break;
                }
            }
        } while (true);

        if (count($migrations) <= 0) {
            $this->warn("Migration cannot be generated. pls try again!");
            exit();
        }

        return array($modelName, $defaultModelDirectory, $modelPath, $migrations);
    }

    /**
     * @param ModelServiceBuilder $model
     * @return MigrationServiceBuilder
     */
    protected function getMigrationBuilder(ModelServiceBuilder $model): MigrationServiceBuilder
    {
        $migrationBuilder = new MigrationServiceBuilder($model);
        $migrationBuilder->setMigrationDependencies([
            'use Illuminate\Support\Facades\Schema',
            'use Illuminate\Database\Schema\Blueprint',
            'use Illuminate\Database\Migrations\Migration',
        ]);
        return $migrationBuilder;
    }

    /**
     * @param FileWriterDirector $migrationFileWriter
     * @return array
     */
    protected function getMigrationDirectory(FileWriterDirector $migrationFileWriter): array
    {
        $migrationFulPath = $migrationFileWriter->getFileWriter()::getDefaultDirectory();
        $filePath = $migrationFulPath . DIRECTORY_SEPARATOR . $migrationFileWriter->getFileName();
        return array($migrationFulPath, $filePath);
    }
}
