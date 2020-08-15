<?php

namespace App\Services;

use App\Commands\LaraCrudCommand;
use App\Contracts\ConstantInterface;
use App\Contracts\FileWriterAbstractFactory;

class InputReaderService implements ConstantInterface
{
    private $laraCrudCommand;

    public function __construct(LaraCrudCommand $laraCrudCommand)
    {
        $this->laraCrudCommand = $laraCrudCommand;
    }

    /**
     * @return array
     */
    public function inputReader(): array
    {
        $migrations = ['id' => ['field_type' => 'increments'],];

        $modelName = strip_tags($this->laraCrudCommand->argument('name'));
        if (empty($modelName)) {
            $this->laraCrudCommand->error("Name argument is missing");
        }

        [$defaultModelDirectory, $modelPath] = $this->getModelDirectoryInfo($modelName);
        if (FileWriterAbstractFactory::fileExists($modelPath)) {
            $this->laraCrudCommand->error("{$modelPath} already exist");
            exit();
        }

        do {
            $dbFieldName = $this->getModelValue($this->askForFieldName());
            if (!empty($dbFieldName) && 'exit' !== $dbFieldName && 'no' !== $dbFieldName) {
                $dbColumnFieldType = $this->userWillSelectColumnFieldType();
                $migrations = $this->setMigrations($migrations, $dbFieldName, $dbColumnFieldType);
            }
            if ('exit' === $dbFieldName) {
                if ($this->laraCrudCommand->confirm('Are you sure you want to exit?', static::EXIT_CONSOLE)) {
                    break;
                }
            }
        } while (true);

        if (count($migrations) <= 0) {
            $this->laraCrudCommand->warn('Migration cannot be generated. pls try again!');
            exit();
        }

        return [$modelName, $defaultModelDirectory, $modelPath, $migrations];
    }

    /**
     * @return string
     */
    private function userWillSelectColumnFieldType(): string
    {
        return $this->laraCrudCommand->choice(
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
        return str_replace(' ', '_', $this->laraCrudCommand->ask('Enter field name'));
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
        if ('enum' === $dbColumnFieldType) {
            $enumValues = $this->getEnumValue(
                str_replace(
                    ' ',
                    '',
                    trim($this->laraCrudCommand->ask('Enter enum values separated by a comma'))
                )
            );
            if (empty($enumValues)) {
                $this->laraCrudCommand->error('field name is missing');
            }
            if (!empty($enumValues)) {
                $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'values' => explode(',', $enumValues)];
            }
        }

        if ('enum' !== $dbColumnFieldType) {
            $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType];
        }

        if (str_contains($dbColumnFieldType, 'string') || str_contains($dbColumnFieldType, 'integer')) {
            $fieldLength = (int)trim($this->laraCrudCommand->ask('Enter the length'));
            $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'length' => $fieldLength];

            if (empty($fieldLength)) {
                $this->laraCrudCommand->info('Default length will be used instead');
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
        $modelDirectory = $this->laraCrudCommand->option('folder');
        $applicationNamespace = ucwords(explode('\\', static::class)[0]);
        $defaultModelDirectory = FileWriterAbstractFactory::getDefaultDirectory($modelDirectory, $applicationNamespace);
        $modelPath = FileWriterAbstractFactory::getWorkingDirectory($defaultModelDirectory, $modelName);

        return [$defaultModelDirectory, $modelPath];
    }

    /**
     * @param $input
     * @return mixed
     */
    protected function getModelValue(string $input): string
    {
        $fieldName = '';
        $pattern = '/^([A-Za-z\s])*\w+/i';
        preg_match($pattern, $input, $matches);

        if (isset($matches[0])) {
            $fieldName = $matches[0];
        }

        return $fieldName;
    }

    protected function getEnumValue(string $input): string
    {
        $fieldName = '';
        $pattern = '/^([A-Za-z\s,])+\w+/i';
        preg_match($pattern, $input, $matches);

        if (isset($matches[0])) {
            $fieldName = $matches[0];
        }

        return $fieldName;
    }
}
