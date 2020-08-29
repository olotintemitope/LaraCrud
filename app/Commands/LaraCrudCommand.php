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
    protected $signature = 'make:crud {name : The name of the model (required)}
        {--f= : The path to the model folder (optional)}
        {--m= : The model mode which can create|update (optional)}
        {--g= : Optional parameter for generating either model or migration. But the default mode is create (optional)}
        {--mf= : Migration file name (optional)}
        {--d= : Dump the model and migration file content (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a crud operation out of your console';

    /** @var InputReaderService */
    private  $inputReaderService;

    public function __construct()
    {
        parent::__construct();
        $this->inputReaderService = new InputReaderService($this, new ModelFileWriterService);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            [$modelName, $defaultModelDirectory, $modelPath, $migrations, $writerOption, $modelOption, $migrationFilename, $dumpContent] = $this->inputReader();
            $modelNamespace = str_replace('/', '\\', $defaultModelDirectory);

            [$modelOutputDirector, $modelFileWriter, $migrationBuilder] = $this->buildModel($modelName, $migrations, $modelNamespace, $modelOption);
            [$migrationOutputDirector, $migrationWriter, $migrationFulPath, $filePath] = $this->buildMigration($migrationBuilder, $migrationFilename, $modelName);

            if (static::CRUD_MODEL_ONLY === $writerOption || is_null($writerOption)) {
                if (null === $dumpContent) {
                    $this->info($modelOutputDirector->getFileContent());
                }
                if (null !== $dumpContent) {
                    // Write to molder folder
                    $modelFileWriter->getWriter()::write($defaultModelDirectory, $modelPath, $modelOutputDirector->getFileContent());
                    $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");
                }
            }
            if (static::CRUD_MIGRATION_ONLY === $writerOption || is_null($writerOption)) {
                $migrationBuilder->setClassName($migrationWriter->getFileName());
                if (null === $dumpContent) {
                    $this->info($migrationOutputDirector->getFileContent());
                }
                if (null !== $dumpContent) {
                    //Write to migration folder
                    $migrationWriter->getWriter()::write($migrationFulPath, $filePath, $migrationOutputDirector->getFileContent());
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

        $softDeletes = array_filter(array_values($model->getMigrations()), function ($migration) {
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

    /**
     * @param $modelName
     * @param $migrations
     * @param string $modelNamespace
     * @param $modelOption
     * @return array
     */
    protected function buildModel($modelName, $migrations, string $modelNamespace, $modelOption): array
    {
        $modelBuilder = $this->getModelBuilder($modelName, $migrations, $modelNamespace);
        $modelOutputDirector = new OutPutDirector($modelBuilder);
        $modelFileWriter = new ModelFileWriterService;
        $modelWriterDirector = new FileWriterDirector($modelFileWriter);

        $modelWriterDirector->getWriter()->setFileName($modelName);
        $migrationBuilder = $this->getMigrationBuilder($modelBuilder);
        $migrationBuilder->setSchemaMode($modelOption);

        return [$modelOutputDirector, $modelFileWriter, $migrationBuilder];
    }

    /**
     * @param $migrationBuilder
     * @param $migrationFilename
     * @param $modelName
     * @return array
     */
    protected function buildMigration($migrationBuilder, $migrationFilename, $modelName): array
    {
        $migrationOutputDirector = new OutPutDirector($migrationBuilder);
        $migrationFileWriter = new MigrationFileWriterService;
        $migrationWriter = new FileWriterDirector($migrationFileWriter);

        $migrationWriter->setFileName($migrationFilename ?? $modelName);
        [$migrationFulPath, $filePath] = $migrationFileWriter->getDirectory($migrationWriter);

        return [$migrationOutputDirector, $migrationWriter, $migrationFulPath, $filePath];
    }
}
