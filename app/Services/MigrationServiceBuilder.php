<?php


namespace App\Services;


use App\Contracts\ConstantInterface;
use App\Contracts\MigrationServiceInterface;
use App\Contracts\ModelServiceInterface;
use App\Traits\OutPutWriterTrait;

class MigrationServiceBuilder implements ConstantInterface, MigrationServiceInterface
{
    use OutPutWriterTrait;

    /**
     * @var ModelServiceBuilder
     */
    private $modelService;
    /**
     * @var string
     */
    private $migrationDependencies;

    /**
     * MigrationServiceBuilder constructor.
     * @param ModelServiceInterface $model
     */
    public function __construct(ModelServiceInterface $model)
    {
        $this->modelService = $model;
    }

    /**
     * @param array $namespaces
     * @return MigrationServiceBuilder
     */
    public function setMigrationDependencies(array $namespaces): MigrationServiceBuilder
    {
        $this->migrationDependencies = implode(";" . PHP_EOL, $namespaces);
        return $this;
    }

    /**
     * @return string
     */
    public function getMigrationDependencies(): string
    {
        return $this->migrationDependencies . static::END_OF_LINE;
    }

    /**
     * @return string
     */
    public function getClassDefinition(): string
    {
        $migrationClassName = ucwords($this->modelService->getModelName());
        $migrationTableName = "Create{$migrationClassName}Table";

        return $this->writeLine("class {$migrationTableName} extends Migration", 0) .
            $this->writeLine("{", 0);
    }

    /**
     * @param string $table
     * @return string
     */
    public function getMigrationFields($table = '$table'): string
    {
        $tearUp = '';
        foreach ($this->modelService->getMigrations() as $field => $migration) {
            $dataType = $migration['field_type'];
            switch ($dataType) {
                case 'enum':
                    $tearUp .= $this->getEnumFields($migration, $table, $dataType, $field);
                    break;
                default:
                    $tearUp .= $this->getOtherFields($migration, $table, $dataType, $field);
                    break;
            }
        }
        $tearUp .= $this->writeLine('$table->timestamps();', 3);

        return $tearUp;
    }

    /**
     * @param string $table
     * @return string
     */
    public function getSchemaTearUp($table = '$table'): string
    {
        return
            $this->writeLine("public function up()", 1) .
            $this->writeLine("{", 1) .
            $this->writeLine("Schema::create('{$this->modelService->getTableName()}', function (Blueprint $table) {", 2) .
            $this->writeLine($this->getMigrationFields(), 0) .
            $this->writeLine("});", 2) .
            $this->writeLine("}", 1, PHP_EOL);
    }

    /**
     * @return string
     */
    public function getSchemaTearDown(): string
    {
        return
            $this->writeLine("public function down()", 1) .
            $this->writeLine("{", 1) .
            $this->writeLine("Schema::dropIfExists('{$this->modelService->getTableName()}');", 2) .
            $this->writeLine("}", 1, PHP_EOL);
    }

    /**
     * @return string
     */
    public function build(): string
    {
        return
            $this->getStartTag() .
            $this->getMigrationDependencies() .
            $this->getEndOfLine() .
            $this->getClassDefinition() .
            $this->comments(
                'Run the migrations.',
                '',
                '@return void'
            ) .
            $this->getSchemaTearUp() .
            $this->comments(
                'Reverse the migrations.',
                '',
                '@return void'
            ) .
            $this->getSchemaTearDown() .
            $this->getClosingTag();
    }

    /**
     * @param array $migration
     * @param string $table
     * @param $dataType
     * @param string $field
     * @return string
     */
    protected function getEnumFields(array $migration, string $table, $dataType, string $field): string
    {
        $enumValues = implode(",", array_map(function ($field) {
            return "'{$field}'";
        }, $migration['values']));

        return $this->writeLine("$table->{$dataType}('{$field}', [{$enumValues}]);", 3);
    }

    /**
     * @param $migration
     * @param string $table
     * @param $dataType
     * @param string $field
     * @return string
     */
    protected function getOtherFields($migration, string $table, $dataType, string $field): string
    {
        if (isset($migration['length']) && $migration['length'] > 0) {
            $length = $migration['length'];
            return $this->writeLine("$table->{$dataType}('{$field}', {$length});", 3);
        }

        return $this->writeLine("$table->{$dataType}('{$field}');", 3);
    }

}
