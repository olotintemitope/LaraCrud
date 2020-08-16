<?php

namespace Laztopaz\Builders;

use Laztopaz\Contracts\AbstractBuilderServiceCommon;
use Laztopaz\Contracts\BuilderServiceInterface;
use Laztopaz\Contracts\ConstantInterface;
use Laztopaz\Contracts\FileWriterAbstractFactory;
use Laztopaz\Traits\OutPutWriterTrait;

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

    /**
     * @return string
     */
    public function getMigrationDependencies(): string
    {
        return $this->migrationDependencies . static::END_OF_LINE . $this->getNewLine();
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
    public function getClassDefinition(): string
    {
        $migrationClassName = ucwords($this->modelService->getModelName());
        $migrationTableName = "Create{$migrationClassName}Table";

        return $this->writeLine("class {$migrationTableName} extends Migration", 0) .
            $this->writeLine("{", 0);
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
     * @param string $table
     * @return string
     */
    public function getMigrationFields($table = '$table'): string
    {
        $schemaTearUpFields = '';
        $migrationFields = '';

        foreach ($this->modelService->getMigrations() as $field => $migration) {
            $fieldType = $migration['field_type'];
            if (static::ENUM === $fieldType) {
                $schemaTearUpFields .= $this->getEnumFields($migration, $table, $fieldType, $field);
            } else {
                $schemaTearUpFields .= $this->getOtherFields($migration, $table, $fieldType, $field);
            }
        }

        $migrationFields = $this->getDefaultFields($schemaTearUpFields, $table, $migrationFields);

        return $migrationFields;
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

        if (in_array($dataType, static::FIELD_TYPES_WITHOUT_ARGUMENTS, true)) {
            return $this->writeLine("$table->{$dataType}();", 3);
        }

        return $this->writeLine("$table->{$dataType}('{$field}');", 3);
    }

    /**
     * Add increment and timestamp
     *
     * @param string $schemaTearUpFields
     * @param string $table
     * @param string $migrationFields
     * @return string
     */
    protected function getDefaultFields(string $schemaTearUpFields, string $table, string $migrationFields): string
    {
        if (!str_contains(strtolower($schemaTearUpFields), 'increments')) {
            $increments = 'increments';
            $migrationFields .= $this->writeLine("$table->{$increments}('id');", 3);
        }

        $migrationFields .= $schemaTearUpFields;

        if (!str_contains(strtolower($schemaTearUpFields), 'timestamps')) {
            $migrationFields .= $this->writeLine('$table->timestamps();', 3);
        }
        return $migrationFields;
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
}
