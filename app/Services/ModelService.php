<?php


namespace App\Services;


use ICanBoogie\Inflector;
use LaravelZero\Framework\Commands\Command;

class ModelService extends Command
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
        $content .= "\r\tprotected $casts = [\n\r\t];\r";
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
}
