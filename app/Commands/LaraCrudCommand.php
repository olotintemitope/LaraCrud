<?php

namespace App\Commands;

use App\Builders\MigrationServiceBuilder;
use App\Builders\ModelServiceBuilder;
use App\Directors\FileWriterDirector;
use App\Directors\OutPutDirector;
use App\Services\InputReaderService;
use App\Services\MigrationFileWriterService;
use App\Services\ModelFileWriterService;
use App\Contracts\ConstantInterface;
use Exception;
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
            [$modelName, $defaultModelDirectory, $modelPath, $migrations] = $this->inputReader();
            $modelNamespace = str_replace('/', '\\', $defaultModelDirectory);

            if (!empty($modelName)) {
                $modelBuilder = $this->getModelBuilder($modelName, $migrations, $modelNamespace);
                $fileOutputDirector = new OutPutDirector($modelBuilder);
                $fileWriter = new FileWriterDirector($modelFileWriter);
                // Write to molder folder
                $fileWriter::write($defaultModelDirectory, $modelPath, $fileOutputDirector->getFileContent());
                $this->info("{$modelName} was created for you and copied to the {$defaultModelDirectory} folder");

                $migrationBuilder = $this->getMigrationBuilder($modelBuilder);
                $fileOutputDirector = new OutPutDirector($migrationBuilder);
                $fileWriter = new FileWriterDirector($migrationFileWriter);
                $fileWriter->setFileName(strtolower("create_{$modelName}_table"));
                [$migrationFulPath, $filePath] = $this->getMigrationDirectory($fileWriter);
                 //Write to migration folder
                $fileWriter::write($migrationFulPath, $filePath, $fileOutputDirector->getFileContent());
                $this->info("{$modelName} migrations was generated for you and copied to the {$migrationFulPath} folder");
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
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
     * @return array
     */
    protected function inputReader(): array
    {
        return $this->inputReaderService->inputReader();
    }

    /**
     * @param ModelServiceBuilder $model
     * @return MigrationServiceBuilder
     */
    protected function getMigrationBuilder(ModelServiceBuilder $model): MigrationServiceBuilder
    {
        $migrationBuilder = new MigrationServiceBuilder($model);
        $migrationBuilder->setMigrationDependencies([
            'use Illuminate\Support\Facades\Schema',
            'use Illuminate\Database\Schema\Blueprint',
            'use Illuminate\Database\Migrations\Migration',
        ]);
        return $migrationBuilder;
    }

    /**
     * @param FileWriterDirector $fileWriterDirector
     * @return array
     */
    public function getMigrationDirectory(FileWriterDirector $fileWriterDirector): array
    {
        $migrationFulPath = $fileWriterDirector->getFileWriter()::getDefaultDirectory();
        $filePath = $migrationFulPath . DIRECTORY_SEPARATOR . $fileWriterDirector->getFileName();
        return array($migrationFulPath, $filePath);
    }
}
