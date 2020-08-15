<?php

namespace App\Services;

use App\Commands\LaraCrudCommand;
use App\Contracts\ConstantInterface;

class InputReaderService implements ConstantInterface
{
    private $laraCrudCommand;
    /**
     * @var ModelFileWriterService
     */
    private $modelWriter;

    public function __construct(LaraCrudCommand $laraCrudCommand, ModelFileWriterService $writer)
    {
        $this->laraCrudCommand = $laraCrudCommand;
        $this->modelWriter = $writer;
    }

    /**
     * @return array
     */
    public function inputReader(): array
    {
        $migrations = ['id' => ['field_type' => 'increments'],];

        $modelName = $this->getModelNameValue($this->laraCrudCommand->argument('name'));
        if (empty($modelName)) {
            $this->laraCrudCommand->error("Name argument is missing");
        }

        $modelDirectory = $this->laraCrudCommand->option('folder');
        [$defaultModelDirectory, $modelPath] = $this->modelWriter::getDirectoryInfo($modelName, $modelDirectory);

        if ($this->modelWriter::fileExists($modelPath)) {
            $this->laraCrudCommand->error("{$modelPath} already exist");
            exit();
        }

        do {
            $dbFieldName = $this->getModelFieldValue($this->askForFieldName());
            if (!empty($dbFieldName) && static::EXIT !== $dbFieldName && static::NO_PLEASE !== $dbFieldName) {
                $dbColumnFieldType = $this->userWillSelectColumnFieldType();
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
            $defaultIndex = static::STRING_FIELD,
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
        if (static::ENUM === $dbColumnFieldType) {
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

        if (static::ENUM !== $dbColumnFieldType) {
            $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType];
        }

        if (str_contains($dbColumnFieldType, 'string') || str_contains($dbColumnFieldType, 'integer')) {
            $fieldLength = (int)trim($this->laraCrudCommand->ask('Enter the length'));
            if (empty($fieldLength)) {
                $this->laraCrudCommand->info('Default length will be used instead');
            }
            $migrations[($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'length' => $fieldLength];
        }

        return $migrations;
    }

    /**
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
     * @param $input
     * @return string
     */
    protected function getModelNameValue($input): string
    {
        $pattern = '/^([A-Za-z])\w+/i';
        preg_match($pattern, $input, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }
}
