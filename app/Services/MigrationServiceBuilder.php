<?php


namespace app\Services;


use App\Contracts\ConstantInterface;
use App\Contracts\MigrationServiceInterface;
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
     * @param ModelServiceBuilder $model
     */
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
        $this->migrationDependencies = implode(";" . PHP_EOL, $namespaces);
        return $this;
    }

    /**
     * @return string
     */
    public function getMigrationDependencies(): string
    {
        return $this->migrationDependencies . $this->getEndOfLine();
    }

    public function getClassDefinition(): string
    {
        $migrationClassName = ucwords($this->modelService->getModelName());
        $migrationTableName = "Create{$migrationClassName}Table";
        return "class {$migrationTableName} extends Migration" . PHP_EOL . "{" . $this->getCarriageReturn();
    }

    public function getMigrationFields($table = '$table'): string
    {
        $ups = '';
        foreach ($this->modelService->getMigrations() as $field => $migration) {
            $dataType = $migration['field_type'];
            switch ($dataType) {
                case 'enum':
                    $ups .= $this->getEnumFields($migration, $table, $dataType, $field, $ups);
                    break;
                default:
                    [$otherFields] = $this->getOtherFields($migration, $table, $dataType, $field, $ups);
                    $ups .= $otherFields;
                    break;
            }
        }
        $ups .= '$table->timestamps();';

        return $ups;
    }

    /**
     * @param string $table
     * @return string
     */
    public function getSchemaTearUp($table = '$table'): string
    {
        return $this->getDoubleTab() . "public function up()" . PHP_EOL . "{" .
            $this->getCarriageReturn() .
            $this->getDoubleTab() .
            "Schema::create('{$this->modelService->getTableName()}', function (Blueprint $table)" .
            $this->getCarriageReturn() . "{" .
            $this->getMigrationFields() .
            $this->getDoubleTab(). "});" .
            $this->getClosingTag();
    }

    /**
     * @return string
     */
    public function getSchemaTearDown(): string
    {
       return $this->getDoubleTab(). "public function down()" .PHP_EOL. "{".
           $this->getDoubleTab(). "Schema::dropIfExists('{$this->modelService->getTableName()}')". $this->getEndOfLine() .
           $this->getClosingTag();
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
     * @param $migration
     * @param string $table
     * @param $dataType
     * @param int $field
     * @param string $ups
     * @return string
     */
    protected function getEnumFields($migration, string $table, $dataType, int $field, string $ups): string
    {
        $enumValues = implode(",", array_map(function ($field) {
            return "'{$field}'";
        }, $migration));
        $ups .= $this->getDoubleTab() . "$table->{$dataType}('{$field}', [{$enumValues}]);" . $this->getEndOfLine();
        return $ups;
    }

    /**
     * @param $migration
     * @param string $table
     * @param $dataType
     * @param string $field
     * @param string $ups
     * @return array
     */
    protected function getOtherFields($migration, string $table, $dataType, string $field, string $ups): array
    {
        if (isset($migration['length']) && $migration['length'] > 0) {
            $length = $migration['length'];
            $ups .= $this->getDoubleTab() . "$table->{$dataType}('{$field}', {$length});" . $this->getEndOfLine();
        } else {
            $ups .= $this->getDoubleTab() . "$table->{$dataType}('{$field}');" . $this->getEndOfLine();
        }
        return [$ups];
    }

}
