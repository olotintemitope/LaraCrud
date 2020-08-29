<?php

namespace Laztopaz\Services;

use Illuminate\Support\Str;
use Laztopaz\Commands\LaraCrudCommand;
use Laztopaz\Contracts\ConstantInterface;
use Laztopaz\Contracts\FileWriterAbstractFactory;

class InputReaderService implements ConstantInterface
{
    private $laraCrudCommand;
    /**
     * @var ModelFileWriterService
     */
    private $modelWriter;

    public function __construct(LaraCrudCommand $laraCrudCommand, FileWriterAbstractFactory $writer)
    {
        $this->laraCrudCommand = $laraCrudCommand;
        $this->modelWriter = $writer;
    }

    /**
     * Get all the console inputs
     *
     * @return array
     */
    public function inputReader(): array
    {
        $migrations = [];

        [$writerOption, $modelOption, $modelName, $modelPath, $defaultModelDirectory, $migrationFilename, $dumpContent] = $this->validationOptionalParameters();

        do {
            $dbFieldName = strtolower($this->getModelFieldValue($this->askForFieldName()));
            if (!empty($dbFieldName) && static::EXIT !== $dbFieldName && static::NO_PLEASE !== $dbFieldName) {
                $dbColumnFieldType = $this->userWillSelectFieldType();
                $migrations = $this->setMigrations($migrations, $dbFieldName, $dbColumnFieldType);
            }
            if (static::EXIT === $dbFieldName) {
                if ($this->laraCrudCommand->confirm('Are you sure you want to exit?', static::YES_PLEASE)) {
                    break;
                }
            }
        } while (true);

        if (count($migrations) <= 0) {
            $this->laraCrudCommand->warn('Migration cannot be generated. pls try again!');
            exit();
        }

        return [$modelName, $defaultModelDirectory, $modelPath, $migrations, $writerOption, $modelOption, $migrationFilename, $dumpContent];
    }

    /**
     * Validate and return model name value
     *
     * @param $input
     * @return string
     */
    protected function getModelNameValue($input): string
    {
        $pattern = '/^([A-Za-z])\w+/i';
        preg_match($pattern, $input, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }

    /**
     * Validate and return model field value
     *
     * @param $input
     * @return mixed
     */
    protected function getModelFieldValue(string $input): string
    {
        $pattern = '/^([A-Za-z\s])*\w+/i';
        preg_match($pattern, $input, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }

    /**
     * Validate and return enum field value
     *
     * @param string $input
     * @return string
     */
    protected function getEnumValue(string $input): string
    {
        $pattern = '/^([A-Za-z\s,])+\w+/i';
        preg_match($pattern, $input, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }

    /**
     * Get the field name from the console
     *
     * @return string
     */
    private function askForFieldName(): string
    {
        return $this->userWillEnterFieldName();
    }

    /**
     * Get the field name from the console
     *
     * @return string
     */
    private function userWillEnterFieldName(): string
    {
        return str_replace(' ', '_', $this->laraCrudCommand->ask('Enter field name'));
    }

    /**
     * Get options from the console
     *
     * @return string
     */
    private function userWillSelectFieldType(): string
    {
        return $this->laraCrudCommand->choice(
            'Select field type',
            array_keys(static::AVAILABLE_COLUMN_TYPES),
            $defaultIndex = static::STRING_FIELD,
            $maxAttempts = null,
            $allowMultipleSelections = false
        );
    }

    /**
     * Set all the console input into a migration variable
     * that help define each field properties
     *
     * @param $migrations
     * @param $dbFieldName
     * @param $dbFieldType
     * @return mixed
     */
    public function setMigrations(array $migrations, string $dbFieldName, string $dbFieldType): array
    {
        if (static::ENUM === $dbFieldType) {
            $enumValues = $this->getEnumValue(
                str_replace(
                    ' ',
                    '',
                    trim($this->laraCrudCommand->ask('Enter the ENUM values separated by a comma'))
                )
            );
            if (empty($enumValues)) {
                $this->laraCrudCommand->error('field name is missing');
            }
            if (!empty($enumValues)) {
                $migrations[($dbFieldName)] = ['field_type' => $dbFieldType, 'values' => explode(',', $enumValues)];
            }
        }

        if (static::ENUM !== $dbFieldType) {
            $migrations[($dbFieldName)] = ['field_type' => $dbFieldType];
        }

        if (Str::contains($dbFieldType, 'string') || Str::contains($dbFieldType, 'integer')) {
            $fieldLength = (int)trim($this->laraCrudCommand->ask('Enter the length'));
            if (empty($fieldLength)) {
                $this->laraCrudCommand->info('Default length will be used instead');
            }
            $migrations[($dbFieldName)] = ['field_type' => $dbFieldType, 'length' => $fieldLength];
        }

        return $migrations;
    }

    /** Validate the optional parameters
     *
     * @return string[]
     */
    protected function validationOptionalParameters(): array
    {
        $writerOption = $this->laraCrudCommand->option('g');
        $modelOption = $this->laraCrudCommand->option('m');
        $modelDirectory = $this->laraCrudCommand->option('f');
        $migrationFilename = $this->laraCrudCommand->option('mf');
        $dumpContent = $this->laraCrudCommand->option('d');

        $modelName = $this->getModelNameValue($this->laraCrudCommand->argument('name'));
        [$defaultModelDirectory, $modelPath] = $this->modelWriter::getDirectoryInfo($modelName, $modelDirectory);

        if (empty($modelName)) {
            $this->laraCrudCommand->error("Name argument is missing");
            exit();
        }

        if (static::CRUD_MIGRATION_ONLY !== $writerOption && $this->modelWriter::fileExists($modelPath)) {
            $this->laraCrudCommand->error("{$modelPath} already exist");
            exit();
        }

        if (!is_null($writerOption) && !in_array($writerOption, static::CRUD_GENERATOR_OPTIONS, true)) {
            $this->laraCrudCommand->warn("model|migration options expected for optional --g");
            exit();
        }

        if (!is_null($modelOption) && !in_array($modelOption, self::CRUD_MIGRATION_SCHEMA_OPTIONS, true)) {
            $this->laraCrudCommand->warn("create|update options expected for optional --m");
            exit();
        }

        if (!is_null($migrationFilename)) {
            $migrationFilename = $this->getModelNameValue($migrationFilename);
        }

        if (!is_null($dumpContent) && "true" !== $dumpContent) {
            $this->laraCrudCommand->warn("expects you to pass true to the --d option");
            exit();
        }

        return [$writerOption, $modelOption, $modelName, $modelPath, $defaultModelDirectory, $migrationFilename, (bool) $dumpContent];
    }
}
