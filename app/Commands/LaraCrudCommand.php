<?php

namespace App\Commands;

use App\Contracts\ConstantInterface;
use App\Services\FileWriter;
use App\Services\ModelService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use ICanBoogie\Inflector;

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
     * @param ModelService $model
     * @return mixed
     */
    public function handle(FileWriter $writer, ModelService $model)
    {
        $migrations = [];

        $modelName = strip_tags($this->argument('name'));
        if (empty($modelName)) {
            $this->error("Name argument is missing");
        }

        try {
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

            $content = "";
            $modelDirectory = $this->option('folder');
            $applicationNamespace = ucwords(explode("\\", static::class)[0]);
            $defaultModelDirectory = $writer::getDefaultModelDirectory($modelDirectory, $applicationNamespace);

            if (!empty($modelName)) {
                $capitalizedModelNamespace = str_replace('/', '\\', $defaultModelDirectory);
                $content = $model->write($capitalizedModelNamespace, $modelName, $migrations);
            }

            $modelPath = $writer::getModelWorkingDirectory($defaultModelDirectory, $modelName);
            $writer::write($defaultModelDirectory, $modelPath, $content);
            $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
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
}
