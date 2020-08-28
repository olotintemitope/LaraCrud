<?php

namespace Laztopaz\Builders;

use ICanBoogie\Inflector;
use Illuminate\Support\Str;
use Laztopaz\Contracts\AbstractBuilderServiceCommon;
use Laztopaz\Contracts\BuilderServiceInterface;
use Laztopaz\Contracts\ConstantInterface;
use Laztopaz\Contracts\FileWriterAbstractFactory;
use Laztopaz\Traits\OutPutWriterTrait;

class ModelServiceBuilder extends AbstractBuilderServiceCommon implements ConstantInterface, BuilderServiceInterface
{
    use OutPutWriterTrait;

    /**
     * @var
     */
    protected $namespace;
    /**
     * @var string
     */
    protected $modelDependencies;
    /**
     * @var string
     */
    private $modelTableDefinition;
    /**
     * @var string
     */
    private $modelName;
    /**
     * @var array
     */
    private $migrationFields;
    /**
     * @var array
     */
    private $migrations;
    /**
     * @var FileWriterAbstractFactory
     */
    private $fileWriterAbstractFactory;

    public function __construct()
    {}

    /**
     * @return string
     */
    public function getNameSpace(): string
    {
        return $this->namespace;
    }

    /**
     * @param $modelNamespace
     * @return ModelServiceBuilder
     */
    public function setNameSpace($modelNamespace): ModelServiceBuilder
    {
        $this->namespace = $this->writeLine("namespace {$modelNamespace};", 0, true);
        return $this;
    }

    /**
     * @return string
     */
    public function getModelDependencies(): string
    {
        return $this->writeLine($this->modelDependencies . static::LINE_TERMINATOR, 0);
    }

    /**
     * @param array $namespaces
     * @return ModelServiceBuilder
     */
    public function setModelDependencies(array $namespaces): ModelServiceBuilder
    {
        $this->modelDependencies = implode(";" . $this->getNewLine(), $namespaces);
        return $this;
    }

    public function getClassDefinition(): string
    {
        return $this->writeLine("class {$this->getModelName()} extends Model", 0) .
            $this->writeLine("{", 0);
    }

    /**
     * @return mixed
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * @param $modelName
     * @return ModelServiceBuilder
     */
    public function setModelName($modelName): ModelServiceBuilder
    {
        $this->modelName = $modelName;
        return $this;
    }

    /**
     * @param string $table
     * @return string
     */
    public function getModelTableDefinition($table = '$table'): string
    {
        return $this->writeLine("protected $table = '{$this->getTableName()}';", 1);
    }

    /**
     * Get the name of the table in the model
     * @return string
     */
    public function getTableName(): string
    {
        $inflector = Inflector::get('en');
        return strtolower($inflector->pluralize($this->getModelName()));
    }

    /**
     * Set the migration fillables
     * @param string $fillable
     * @return string
     */
    protected function getFillableDefinition($fillable = '$fillable'): string
    {
        return
            $this->comments(
                'The attributes that are mass assignable.',
                '',
                '@var array'
            ) .
            $this->writeLine("protected $fillable = [", 1) .
            $this->writeLine("{$this->getFields()},", 0) .
            $this->writeLine("];", 1);
    }

    /**
     * @return string
     */
    protected function getFields(): string
    {
        return implode("," . $this->getNewLine(), array_map(function ($field) {
            return $this->writeLine("'{$field}'", 2, false);
        }, array_filter($this->getMigrationFields(), function ($field) {
                return 'id' !== $field && !Str::contains(strtolower($field), 'softdelete');
            })
        ));
    }

    /**
     * Get the migration fields
     * @return mixed
     */
    protected function getMigrationFields()
    {
        return array_keys($this->getMigrations());
    }

    /**
     * Get migrations
     * @return mixed
     */
    public function getMigrations()
    {
        return $this->migrations;
    }

    /**
     * @param mixed $migrations
     * @return ModelServiceBuilder
     */
    public function setMigrations($migrations): ModelServiceBuilder
    {
        $this->migrations = $migrations;
        return $this;
    }

    /**
     * Set the model casts attributes
     * @param string $casts
     * @return string
     */
    protected function getCastsDefinition($casts = '$casts'): string
    {
        if (empty($this->getCastsField())) {
            return '';
        }

        return
            $this->comments('@var array') .
            $this->writeLine("protected $casts = [", 1) .
            $this->writeLine("{$this->getCastsField()},", 0) .
            $this->writeLine("];", 1);
    }

    /**
     * Cast the database fields to their respectively datatype
     * @return string
     */
    protected function getCastsField(): string
    {
        $casts = [];

        foreach ($this->filterCastsField() as $fieldName => $migration) {
            $dataType = strtolower($migration['field_type']);
            if ($dataType === 'boolean') {
                $dataType = 'bool';
            }
            $casts[] = $this->writeLine("'{$fieldName}' => '{$dataType}'", 2, false);
        }

        return implode("," . $this->getNewLine(), $casts);
    }

    /**
     * Cast the field to datatype
     * @return array
     */
    protected function filterCastsField(): array
    {
        return array_filter($this->getMigrations(), function ($field) {
            return
                strpos($field['field_type'], 'date') !== false ||
                strpos($field['field_type'], 'time') !== false ||
                strpos($field['field_type'], 'boolean') !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Build the model
     * @return string
     */
    public function build(): string
    {
        return
            $this->getStartTag() .
            $this->getNewLine() .
            $this->getNameSpace() .
            $this->getNewLine() .
            $this->getModelDependencies() .
            $this->getNewLine() .
            $this->getClassDefinition() .
            $this->getModelTableDefinition() .
            $this->getNewLine() .
            $this->getFillableDefinition() .
            $this->getNewLine() .
            $this->getCastsDefinition() .
            $this->getNewLine() .
            $this->getClosingTag();
    }

}
