<?php

namespace App\Builders;

use App\Contracts\AbstractBuilderServiceCommon;
use App\Contracts\BuilderServiceInterface;
use App\Contracts\ConstantInterface;
use App\Contracts\FileWriterAbstractFactory;
use App\Traits\OutPutWriterTrait;

class MigrationServiceBuilder extends AbstractBuilderServiceCommon implements ConstantInterface, BuilderServiceInterface
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
     * @var FileWriterAbstractFactory
     */
    private $fileWriterAbstractFactory;

    public function __construct(ModelServiceBuilder $model)
    {
        $this->modelService = $model;
    }

    /**
     * @param array $namespaces
     * @return MigrationServiceBuilder
     */
    public function setMigrationDependencies(array $namespaces): MigrationServiceBuilder
    {
        $this->migrationDependencies = implode(";" . $this->getNewLine(), $namespaces);
        return $this;
    }

    /**
     * @return string
     */
    public function getMigrationDependencies(): string
    {
        return $this->migrationDependencies . static::END_OF_LINE . $this->getNewLine();
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
            $fieldType = $migration['field_type'];
            if (static::ENUM === $fieldType) {
                $tearUp .= $this->getEnumFields($migration, $table, $fieldType, $field);
            } else {
                $tearUp .= $this->getOtherFields($migration, $table, $fieldType, $field);
            }
        }

        return $tearUp;
    }

    /**
     * Get the migration schema up function
     *
     * @param string $table
     * @return string
     */
    public function getSchemaTearUp($table = '$table'): string
    {
        return
            $this->comments(
                'Run the migrations.',
                '',
                '@return void'
            ) .
            $this->writeLine("public function up()", 1) .
            $this->writeLine("{", 1) .
            $this->writeLine("Schema::create('{$this->modelService->getTableName()}', function (Blueprint $table) {", 2) .
            $this->writeLine($this->getMigrationFields(), 0) .
            $this->writeLine("});", 2) .
            $this->writeLine("}", 1);
    }

    /**
     * Get the migration schema down function
     *
     * @return string
     */
    public function getSchemaTearDown(): string
    {
        return
            $this->comments(
                'Reverse the migrations.',
                '',
                '@return void'
            ) .
            $this->writeLine("public function down()", 1) .
            $this->writeLine("{", 1) .
            $this->writeLine("Schema::dropIfExists('{$this->modelService->getTableName()}');", 2) .
            $this->writeLine("}", 1);
    }

    /**
     * Get enum field values from the console
     *
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
     * Get fields, length and datatype from the console
     *
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

        if (in_array($dataType, static::COLUMN_TYPES_WITHOUT_ARGUMENTS, true)) {
            return $this->writeLine("$table->{$dataType}();", 3);
        }

        return $this->writeLine("$table->{$dataType}('{$field}');", 3);
    }

    /**
     * build the complete migration file content
     *
     * @return string
     */
    public function build(): string
    {
        return
            $this->getStartTag() .
            $this->getNewLine() .
            $this->getMigrationDependencies() .
            $this->getNewLine() .
            $this->getClassDefinition() .
            $this->getSchemaTearUp() .
            $this->getNewLine() .
            $this->getSchemaTearDown() .
            $this->getNewLine() .
            $this->getClosingTag();
    }
}
