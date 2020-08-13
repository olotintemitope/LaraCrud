<?php


namespace App\Services;

use app\Contracts\BuilderServiceTrait;
use App\Contracts\ConstantInterface;
use App\Traits\OutPutWriterTrait;
use ICanBoogie\Inflector;

class ModelService implements ConstantInterface, BuilderServiceTrait
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
        return implode("," . static::PHP_CRT, array_map(function ($field) {
                return "{$this->getDoubleTab()}'{$field}'";
            }, $this->getMigrationFields())
        );
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
        $filteredMigrations = $this->filterCastsField();

        foreach ($filteredMigrations as $fieldName => $migration) {
            $dataType = strtolower($migration['field_type']);
            if ($dataType === 'boolean') {
                $dataType = 'bool';
            }
            $casts[] = $this->getDoubleTab()."'{$fieldName}' => '{$dataType}'";
        }

        return implode(",".$this->getCarriageReturn(), $casts);
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
     * @return ModelService
     */
    public function setNameSpace($modelNamespace): ModelService
    {
        $this->namespace = "namespace {$modelNamespace};" . $this->getEndOfLine();
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
     * @return ModelService
     */
    public function setModelDependencies(array $namespaces): ModelService
    {
        $this->modelDependencies = implode(";" . PHP_EOL, $namespaces);
        return $this;
    }

    /**
     * @return string
     */
    public function getModelDependencies(): string
    {
        return $this->modelDependencies . static::END_OF_LINE;
    }

    /**
     * @param string $table
     * @return string
     */
    public function getModelTableDefinition($table = '$table'): string
    {
        return $this->getTabAlignment() . "protected $table = '{$this->getTableName()}';" . $this->getEndOfLine();
    }

    /**
     * @param $modelName
     * @return ModelService
     */
    public function setModelName($modelName): ModelService
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
        return "class {$this->getModelName()} extends Model" . PHP_EOL . "{" . static::PHP_CRT;
    }

    protected function getFillableDefinition($fillable = '$fillable'): string
    {
        return
            $this->getTabAlignment() .
            "protected $fillable = [" .
            $this->getCarriageReturn() .
            "{$this->getFields()}," .
            $this->getTabAndCarriageReturn() . "];" . $this->getEndOfLine();
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
            $this->getTabAlignment() .
            "protected $casts = [" .
            $this->getCarriageReturn() .
            "{$this->getCastsField()}," .
            $this->getTabAndCarriageReturn() . "];" . $this->getEndOfLine();
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
     * @return ModelService
     */
    public function setMigrations($migrations): ModelService
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
            $this->getEndOfLine() .
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
