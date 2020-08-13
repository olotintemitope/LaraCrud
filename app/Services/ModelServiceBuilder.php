<?php


namespace App\Services;

use App\Contracts\ConstantInterface;
use App\Contracts\ModelServiceInterface;
use App\Traits\OutPutWriterTrait;
use ICanBoogie\Inflector;

class ModelServiceBuilder implements ConstantInterface, ModelServiceInterface
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
     * @return string
     */
    protected function getFields(): string
    {
        return implode(",".$this->getCarriageReturn(), array_map(function ($field) {
            return $this->writeLine("'{$field}'", 2, false, false);
        }, array_filter($this->getMigrationFields(), function ($field) {
                return 'id' !== $field;
            })
        ));
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
            $casts[] = $this->writeLine("'{$fieldName}' => '{$dataType}'",2, false , false);
        }

        return implode(",".$this->getCarriageReturn() , $casts);
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
    public function getNameSpace(): string
    {
        return $this->namespace;
    }

    /**
     * @param array $namespaces
     * @return ModelServiceBuilder
     */
    public function setModelDependencies(array $namespaces): ModelServiceBuilder
    {
        $this->modelDependencies = implode(";" . PHP_EOL, $namespaces);
        return $this;
    }

    /**
     * @return string
     */
    public function getModelDependencies(): string
    {
        return $this->writeLine($this->modelDependencies . static::END_OF_LINE, 0 , true);
    }

    /**
     * @param string $table
     * @return string
     */
    public function getModelTableDefinition($table = '$table'): string
    {
        return $this->writeLine("protected $table = '{$this->getTableName()}';", 1, true);
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
     * @return mixed
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function getClassDefinition(): string
    {
        return $this->writeLine("class {$this->getModelName()} extends Model", 0) .
            $this->writeLine("{", 0);
    }

    protected function getFillableDefinition($fillable = '$fillable'): string
    {
        return
            $this->writeLine("protected $fillable = [", 1).
            $this->writeLine("{$this->getFields()},", 0).
            $this->writeLine("];", 1, PHP_EOL);
    }

    /**
     * @return mixed
     */
    protected function getMigrationFields()
    {
        return array_keys($this->getMigrations());
    }

    protected function getCastsDefinition($casts = '$casts'): string
    {
        return
            $this->writeLine("protected $casts = [", 1 ).
            $this->writeLine("{$this->getCastsField()}", 0) .
            $this->writeLine("];", 1);
    }

    /**
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
     * @return string
     */
    public function build(): string
    {
        return
            $this->getStartTag() .
            $this->getNameSpace() .
            $this->getModelDependencies() .
            $this->getClassDefinition() .
            $this->comments('@var array') .
            $this->getModelTableDefinition() .
            $this->comments(
                'The attributes that are mass assignable.',
                '',
                '@var array'
            ) .
            $this->getFillableDefinition() .
            $this->comments('@var array') .
            $this->getCastsDefinition() .
            $this->getClosingTag();
    }
}
