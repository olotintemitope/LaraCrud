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
    protected $description = 'Generate model and migration files out of your console';

    /** @var InputReaderService */
    private $inputReaderService;

    /**
     * LaraCrudCommand constructor.
     */
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

            [$migrationOutputDirector, $migrationWriterDirector, $migrationFulPath, $filePath] = $this->buildMigration($migrationBuilder, $migrationFilename, $modelName, $modelOption);

            if (static::CRUD_MODEL_ONLY === $writerOption || is_null($writerOption)) {
                if ($dumpContent) {
                    $this->info($modelOutputDirector->getFileContent());
                }
                if (false === $dumpContent) {
                    $modelFileWriter->getWriter()::write($defaultModelDirectory, $modelPath, $modelOutputDirector->getFileContent());
                    $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");
                }
            }
            if (static::CRUD_MIGRATION_ONLY === $writerOption || is_null($writerOption)) {
                $migrationBuilder->setClassName($migrationWriterDirector->getWriter()->getFileName());
                if ($dumpContent) {
                    $this->info($migrationOutputDirector->getFileContent());
                }
                if (false === $dumpContent) {
                    $migrationWriterDirector->getWriter()::write($migrationFulPath, $filePath, $migrationOutputDirector->getFileContent());
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

        $migrationBuilder = $this->getMigrationBuilder($modelBuilder);
        //$migrationBuilder->setSchemaMode($modelOption);

        $modelWriterDirector->getWriter()->setFileName($modelName, $modelOption);

        return [$modelOutputDirector, $modelWriterDirector, $migrationBuilder];
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
     * @param $migrationBuilder
     * @param $migrationFilename
     * @param $modelName
     * @param string|null $modelOption
     * @return array
     */
    protected function buildMigration($migrationBuilder, $migrationFilename, $modelName, ?string $modelOption): array
    {
        $migrationOutputDirector = new OutPutDirector($migrationBuilder);
        $migrationFileWriter = new MigrationFileWriterService;
        $migrationWriterDirector = new FileWriterDirector($migrationFileWriter);

        $fileName = $migrationFilename ?? $modelName;
        $migrationBuilder->setSchemaMode($modelOption);

        $migrationWriterDirector->getWriter()
            ->setFileName(
                $fileName,
                $migrationBuilder->getSchemaMode()
            );

        [$migrationFulPath, $filePath] = $migrationFileWriter->getDirectory($migrationWriterDirector);

        return [$migrationOutputDirector, $migrationWriterDirector, $migrationFulPath, $filePath];
    }
}
