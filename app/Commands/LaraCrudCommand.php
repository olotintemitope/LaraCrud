<?php

namespace Laztopaz\Commands;

use Exception;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Laztopaz\Builders\MigrationServiceBuilder;
use Laztopaz\Builders\ModelServiceBuilder;
use Laztopaz\Contracts\ConstantInterface;
use Laztopaz\Directors\FileWriterDirector;
use Laztopaz\Directors\OutPutDirector;
use Laztopaz\Services\InputReaderService;
use Laztopaz\Services\MigrationFileWriterService;
use Laztopaz\Services\ModelFileWriterService;

class LaraCrudCommand extends Command implements ConstantInterface
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:crud
        {name : The name of the model (required)}
        {--f= : The path to the model folder (optional)}
        {--m= : The model mode which can create|update (optional)}
        {--g= : Optional parameter for generating either model or migration. But the default mode is create  (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a crud operation out of your model';
    /** @var InputReaderService */
    private $inputReaderService;

    public function __construct()
    {
        parent::__construct();
        $this->inputReaderService = new InputReaderService($this, new ModelFileWriterService);
    }

    /**
     * Execute the console command.
     *
     * @param ModelFileWriterService $modelFileWriter
     * @param MigrationFileWriterService $migrationFileWriter
     * @return mixed
     */
    public function handle(ModelFileWriterService $modelFileWriter, MigrationFileWriterService $migrationFileWriter)
    {
        try {
            [$modelName, $defaultModelDirectory, $modelPath, $migrations, $writerOption, $modelOption] = $this->inputReader();
            $modelNamespace = str_replace('/', '\\', $defaultModelDirectory);
            if (!empty($modelName)) {
                $modelBuilder = $this->getModelBuilder($modelName, $migrations, $modelNamespace);
                if (static::CRUD_MODEL_ONLY === $writerOption || is_null($writerOption)) {
                    $fileOutputDirector = new OutPutDirector($modelBuilder);
                    $fileWriter = new FileWriterDirector($modelFileWriter);
                    // Write to molder folder
                    $fileWriter::write($defaultModelDirectory, $modelPath, $fileOutputDirector->getFileContent());
                    $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");
                }

                if (static::CRUD_MIGRATION_ONLY === $writerOption || is_null($writerOption)) {
                    $migrationBuilder = $this->getMigrationBuilder($modelBuilder);
                    $migrationBuilder->setSchemaMode($modelOption);

                    $fileOutputDirector = new OutPutDirector($migrationBuilder);
                    $fileWriter = new FileWriterDirector($migrationFileWriter);
                    $fileWriter->setFileName(strtolower("{$migrationBuilder->getSchemaMode()}_{$modelName}_table"));
                    [$migrationFulPath, $filePath] = $migrationFileWriter->getDirectory($fileWriter);
                    //Write to migration folder
                    $fileWriter::write($migrationFulPath, $filePath, $fileOutputDirector->getFileContent());
                    $this->info("{$modelName} migrations was generated for you and copied to the {$migrationFulPath} folder");
                }
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Read all the input from the console
     *
     * @return array
     */
    protected function inputReader(): array
    {
        return $this->inputReaderService->inputReader();
    }

    /**
     * Get the model builder
     *
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
     * Get the migration builder
     *
     * @param ModelServiceBuilder $model
     * @return MigrationServiceBuilder
     */
    protected function getMigrationBuilder(ModelServiceBuilder $model): MigrationServiceBuilder
    {
        $migrationBuilder = new MigrationServiceBuilder($model);
        $dependencies = [
            'use Illuminate\Support\Facades\Schema',
            'use Illuminate\Database\Schema\Blueprint',
            'use Illuminate\Database\Migrations\Migration',
        ];

        $softDeletes = array_filter(array_values($model->getMigrations()), function($migration) {
            return Str::contains(strtolower($migration['field_type']), 'softdeletes');
        });
        if (count($softDeletes) > 0) {
            $dependencies[] = 'use Illuminate\Database\Eloquent\SoftDeletes';
            $migrationBuilder->setTraits([
                'use SoftDeletes',
            ]);
        }

        $migrationBuilder->setMigrationDependencies($dependencies);

        return $migrationBuilder;
    }
}
