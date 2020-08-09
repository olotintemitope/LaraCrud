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
        $content = "";
        $modelName = $this->argument('name');
        $modelDirectory = $this->option('folder');
        $applicationNamespace = ucwords(explode("\\", get_called_class())[0]);

        if (empty($modelName)) {
            $this->error("Name argument is missing");
        }

        while (true) {
            $dbColumnName = $this->ask('Enter column field name');
            if (empty($dbColumnName)) {
                $this->error("column field name is missing");
            }

            if (!empty($dbColumnName)) {
                $dbColumnFieldType = $this->choice(
                    'Select column field type',
                    array_keys(static::AVAILABLE_COLUMN_TYPES),
                    $defaultIndex = 38,
                    $maxAttempts = null,
                    $allowMultipleSelections = false
                );

                $exit = $this->ask('Are you done?');


                // Cater for enum types. $table->enum('level', ['easy', 'hard']);
                // Cater for float or decimal $table->decimal('amount', 8, 2);
                /**
                 * [
                 *   first_name => [string],
                 *   state => [enum => [ado ekiti, owo, ibadan]
                 * ]
                 */

            }
        }

        try {
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
}
