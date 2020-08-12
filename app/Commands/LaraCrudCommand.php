<?php

namespace App\Commands;

use App\Services\ModelOutPutWriter;
use App\Contracts\ConstantInterface;
use App\Services\FileWriter;
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
     * @param FileWriter $writer
     * @param ModelOutPutWriter $outputWriter
     * @return mixed
     */
    public function handle(FileWriter $writer, ModelOutPutWriter $outputWriter)
    {
        $migrations = [];

        try {
            $modelName = strip_tags($this->argument('name'));
            if (empty($modelName)) {
                $this->error("Name argument is missing");
            }

            [$defaultModelDirectory, $modelPath] = $this->getModelDirectoryInfo($writer, $modelName);

            if ($writer::modelExists($modelPath)) {
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

            $modelNamespace = str_replace('/', '\\', $defaultModelDirectory);

            if (!empty($modelName)) {
                $this->setModelDefinition($outputWriter, $modelName, $migrations, $modelNamespace);
            }

            $writer::write($defaultModelDirectory, $modelPath, $outputWriter->buildFileContent());
            $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");
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
            $enumValues = trim($this->ask('Enter enum values separated by a comma'));
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
     * @param FileWriter $writer
     * @param string $modelName
     * @return array
     */
    protected function getModelDirectoryInfo(FileWriter $writer, string $modelName): array
    {
        $modelDirectory = $this->option('folder');
        $applicationNamespace = ucwords(explode("\\", static::class)[0]);
        $defaultModelDirectory = $writer::getDefaultModelDirectory($modelDirectory, $applicationNamespace);
        $modelPath = $writer::geWorkingDirectory($defaultModelDirectory, $modelName);
        return array($defaultModelDirectory, $modelPath);
    }

    /**
     * @param ModelOutPutWriter $outputWriter
     * @param string $modelName
     * @param array $migrations
     * @param string $modelNamespace
     */
    protected function setModelDefinition(ModelOutPutWriter $outputWriter, string $modelName, array $migrations, string $modelNamespace): void
    {
        $outputWriter->getModel()
            ->setModelName($modelName)
            ->setMigrations($migrations)
            ->setNameSpace($modelNamespace)
            ->setModelDependencies([
                'use Illuminate\Database\Eloquent\Model',
            ]);
    }
}
