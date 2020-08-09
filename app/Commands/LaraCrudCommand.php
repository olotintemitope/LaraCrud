<?php

namespace App\Commands;

use App\Contracts\ConstantInterface;
use App\Services\FileWriter;
use Illuminate\Console\Scheduling\Schedule;
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
     * @return mixed
     */
    public function handle(FileWriter $writer)
    {
        $migrations = [];
        $modelName = strip_tags($this->argument('name'));

        if (empty($modelName)) {
            $this->error("Name argument is missing");
        }

        while (true) {
            $dbFieldName = $this->userWillEnterFieldName();
            if (empty($dbFieldName)) {
                $this->error("field name is missing");
                $dbFieldName = $this->userWillEnterFieldName();
            }  if (!empty($dbFieldName)) {
                if ('exit' === strtolower($dbFieldName) &&
                    $this->confirm('Are you sure you want to exit?', self::EXIT_CONSOLE)
                ) {
                    break;
                }
                $dbColumnFieldType = $this->userWillSelectColumnFieldType();
                $migrations = $this->setMigrations($migrations, $dbFieldName, $dbColumnFieldType);
            }
        }

        print_r($migrations);

        try {
            $modelDirectory = $this->option('folder');
            $applicationNamespace = ucwords(explode("\\", static::class)[0]);
            $defaultModelDirectory = $writer::getDefaultModelDirectory($modelDirectory, $applicationNamespace);

            if (!empty($modelName)) {
                $capitalizedModelNamespace = str_replace('/', '\\', $defaultModelDirectory);

                $content = "<?php \n\r namespace {$capitalizedModelNamespace}; \n\r use Illuminate\Database\Eloquent\Model; \n\r class {$modelName} extends Model \n{\n\r}";
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

    private function userWillEnterFieldName()
    {
        return str_replace(' ', '_', $this->ask('Enter field name'));
    }

    /**
     * @param $migrations
     * @param $dbFieldName
     * @param $dbColumnFieldType
     * @return mixed
     */
    private function setMigrations(array $migrations, string $dbFieldName, string $dbColumnFieldType): array
    {
        // Cater for enum types. $table->enum('level', ['easy', 'hard']);
        if ('enum' === $dbColumnFieldType) {
            $enumValues = trim($this->ask('Enter enum values separated by a comma'));
            if (empty($enumValues)) {
                $this->error("field name is missing");
            }
            if (!empty($enumValues)) {
                $migrations[(string)($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'values' => explode(',', $enumValues)];
            }
        }

        if ('enum' !== $dbColumnFieldType) {
            $migrations[(string)($dbFieldName)] = ['field_type' => $dbColumnFieldType];
        }

        if (str_contains($dbColumnFieldType, 'string') || str_contains($dbColumnFieldType, 'integer')) {
            $fieldLength = (int)trim($this->ask('Enter the length'));
            $migrations[(string)($dbFieldName)] = ['field_type' => $dbColumnFieldType, 'length' => $fieldLength];
            if (empty($fieldLength)) {
                $this->info("Default length will be used instead");
            }
        }

        return $migrations;
    }
}
