<?php


namespace App\Services;


use App\Contracts\ConstantInterface;
use ICanBoogie\Inflector;
use LaravelZero\Framework\Commands\Command;

class ModelService extends Command implements ConstantInterface
{
    public function write($capitalizedModelNamespace, $modelName, $migrations): string
    {
        $table = '$table';
        $fillable = '$fillable';
        $casts = '$casts';
        $migrationFields = array_keys($migrations);

        $content = "<?php \n\rnamespace {$capitalizedModelNamespace}; \n\r";
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
     * @param $modelName
     * @return string
     */
    protected function getTableName($modelName): string
    {
        $inflector = Inflector::get('en');
        return strtolower($inflector->pluralize($modelName));
    }

    protected function getFieldCasts(array $migrations): string
    {
        $casts = [];
        $filteredMigrations = array_filter($migrations, function ($field) {
            return
                strpos($field['field_type'],'date') !== false ||
                strpos($field['field_type'],'time') !== false ||
                strpos($field['field_type'],'boolean') !== false
                ;
        }, ARRAY_FILTER_USE_BOTH);

        foreach($filteredMigrations as $fieldName => $migration) {
            $dataType = strtolower($migration['field_type']);
            if ($dataType === 'boolean') {
                $dataType = 'bool';
            }
            $casts[] = "\t\t'{$fieldName}' => '{$dataType}'";
        }

        return implode(",\r", $casts);
    }
}
