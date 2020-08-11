<?php


namespace App\Services;


use App\Contracts\ConstantInterface;
use ICanBoogie\Inflector;
use LaravelZero\Framework\Commands\Command;

class ModelService implements ConstantInterface
{
    protected $namespace = "";
    /**
     * @var string
     */
    protected $modelDependencies = "";

    public function __construct()
    {
    }

    /**
     * @param array $migrationFields
     * @return string
     */
    protected function getFields(array $migrationFields): string
    {
        return implode(",\r", array_map(function ($field) {
                return "\t\t'{$field}'";
            }, $migrationFields)
        );
    }

    /**
     * Get the name of the table in the model
     * @param $modelName
     * @return string
     */
    protected function getTableName($modelName): string
    {
        $inflector = Inflector::get('en');
        return strtolower($inflector->pluralize($modelName));
    }

    /**
     * Cast the database fields to their respectively datatype
     * @param array $migrations
     * @return string
     */
    protected function getFieldCasts(array $migrations): string
    {
        $casts = [];
        $filteredMigrations = $this->filterFieldsToCast($migrations);

        foreach ($filteredMigrations as $fieldName => $migration) {
            $dataType = strtolower($migration['field_type']);
            if ($dataType === 'boolean') {
                $dataType = 'bool';
            }
            $casts[] = "\t\t'{$fieldName}' => '{$dataType}'";
        }

        return implode(",\r", $casts);
    }

    /**
     * Cast the field to datatype
     * @param array $migrations
     * @return array
     */
    protected function filterFieldsToCast(array $migrations): array
    {
        return array_filter($migrations, function ($field) {
            return
                strpos($field['field_type'], 'date') !== false ||
                strpos($field['field_type'], 'time') !== false ||
                strpos($field['field_type'], 'boolean') !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /** Write the model to a file with all the fillables and
     * casts fields
     * @param $modelNamespace
     * @param $modelName
     * @param $migrations
     * @return string
     */
    public function write($modelNamespace, $modelName, $migrations): string
    {
        $table = '$table';
        $fillable = '$fillable';
        $casts = '$casts';
        $migrationFields = array_keys($migrations);

        $content = "<?php \n\rnamespace {$modelNamespace}; \n\r";
        // Import dependencies here
        $content .= "use Illuminate\Database\Eloquent\Model;\n\r";
        $content .= "class {$modelName} extends Model \n{\r";
        $content .= <<<TEXT
        \t/**
         \t* @var string
         \t*/
        TEXT;
        $content .= "\r\tprotected $table = '{$this->getTableName($modelName)}';\n\r";
        $content .= <<<TEXT
        \t/**
         \t* The attributes that are mass assignable.
         \t*
         \t* @var array
         \t*/
        TEXT;
        $content .= "\r\tprotected $fillable = [\r{$this->getFields($migrationFields)},\r\t];\n\r";
        $content .= <<<TEXT
        \t/**
         \t* @var array
         \t*/
        TEXT;
        $content .= "\r\tprotected $casts = [\r{$this->getFieldCasts($migrations)}, \r\t];\n\r";
        $content .= "\r}";

        return $content;
    }

    public function getStartTag(): string
    {
        return "<?php".PHP_EOL.static::PHP_CR;
    }

    public function setNameSpace($modelNamespace): string
    {
        return $this->namespace = "namespace {$modelNamespace}".PHP_EOL.static::PHP_CR;
    }

    public function getNameSpace(): string
    {
        return $this->namespace;
    }

    public function setModelDependencies(array $namespaces) : string
    {
        return $this->modelDependencies = implode(";".PHP_EOL.static::PHP_CR, $namespaces);
    }

    public function getModelDependencies(): string
    {
        return $this->modelDependencies;
    }

    /**
     * @param mixed ...$comments
     * @return string
     */
    public function comments(...$comments): string
    {
        $multiLineComments = '';
        foreach($comments as $lineComment){
            $multiLineComments .= PHP_TAB.' * '.$lineComment.PHP_EOL;
        }

        $startOfComment = static::PHP_TAB.'/**'.PHP_EOL;
        $endOfComment = static::PHP_TAB.' */';

        return $startOfComment.$multiLineComments.$endOfComment;
    }
}
