<?php

namespace Laztopaz\Builders;

use Laztopaz\Contracts\AbstractBuilderServiceCommon;
use Laztopaz\Contracts\BuilderServiceInterface;
use Laztopaz\Contracts\ConstantInterface;
use Laztopaz\Contracts\FileWriterAbstractFactory;
use Laztopaz\Traits\OutPutWriterTrait;
use Illuminate\Support\Str;

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
    /**
     * @var string
     */
    private $traits;
    /**
     * @var string
     */
    private $mode;
    private string $className;

    public function __construct(ModelServiceBuilder $model)
    {
        $this->modelService = $model;
    }

    /**
     * @return string
     */
    public function getMigrationDependencies(): string
    {
        return $this->migrationDependencies . static::LINE_TERMINATOR . $this->getNewLine();
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
        return $this->writeLine("class {$this->getClassName()} extends Migration", 0) .
            $this->writeLine("{", 0, false);
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
            $this->writeLine("Schema::{$this->getSchemaMode()}('{$this->modelService->getTableName()}', function (Blueprint $table) {", 2) .
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
        if ('table' === $this->getSchemaMode()) {
            return $schemaTearUpFields;
        }

        if (!Str::contains(strtolower($schemaTearUpFields), 'increments')) {
            $increments = 'increments';
            $migrationFields .= $this->writeLine("$table->{$increments}('id');", 3);
        }

        $migrationFields .= $schemaTearUpFields;

        if (!Str::contains(strtolower($schemaTearUpFields), 'timestamps')) {
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

    /**
     * Set array of traits to be used by the migration
     * @param array $traits
     * @return $this
     */
    public function setTraits(array $traits): MigrationServiceBuilder
    {
        $this->traits = implode(";" .$this->getNewLine(), $traits);

        return $this;
    }

    /**
     * Get traits
     * @return string
     */
    public function getTraits(): string
    {
        return empty($this->traits)
            ? ''
            : $this->writeLine($this->traits . static::LINE_TERMINATOR, 1);
    }

    /**
     * Set the schema mode. Could be to create
     * a new schema or update an existing one.
     *
     * @param string|null $mode
     * @return $this
     */
    public function setSchemaMode(?string $mode = null): MigrationServiceBuilder
    {
        $this->mode = $mode ?? 'create';
        if (strtolower($mode) === 'update') {
            $this->mode = 'table';
        }
        return $this;
    }

    /**
     * Get the schema mode
     * @return string
     */
    public function getSchemaMode(): string
    {
        return $this->mode;
    }

    /**
     * This sets the class name
     * @param string $filename
     * @return $this
     */
    public function setClassName(string $filename): self
    {
        $this->className = $this->getCapitalizedClassName($filename);
        return $this;
    }

    /**
     * Get the class name
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Get the class name to match with the file name
     * @param string $filename
     * @return string
     */
    private function getCapitalizedClassName(string $filename): string
    {
        preg_match('/([a-zA-Z])\w+/i', $filename, $match);
        $fileName = isset($match[0]) ? $match[0] : null;

        $splitFilename = array_map(function($eachWord) {
            return ucwords($eachWord);
        }, explode('_', $fileName));

        return implode('', $splitFilename);
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
            $this->getTraits() .
            $this->getNewLine() .
            $this->getSchemaTearUp() .
            $this->getNewLine() .
            $this->getSchemaTearDown() .
            $this->getNewLine() .
            $this->getClosingTag();
    }
}
