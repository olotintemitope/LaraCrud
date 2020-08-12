<?php


namespace app\Services;


use App\Contracts\ConstantInterface;
use app\Contracts\MigrationServiceInterface;
use App\Traits\OutPutWriterTrait;

class MigrationService implements ConstantInterface, MigrationServiceInterface
{
    use OutPutWriterTrait;

    /**
     * @var ModelService
     */
    private $modelService;
    /**
     * @var string
     */
    private $migrationDependencies;

    public function __construct(ModelService $model)
    {
        $this->modelService = $model;
    }

    /**
     * @param array $namespaces
     * @return MigrationService
     */
    public function setMigrationDependencies(array $namespaces): MigrationService
    {
        $this->migrationDependencies = implode(";" . PHP_EOL, $namespaces);
        return $this;
    }

    /**
     * @return string
     */
    public function getMigrationDependencies(): string
    {
        return $this->migrationDependencies . static::END_OF_LINE;
    }

    public function buildMigration(): string
    {
        return
            $this->getStartTag() .
            $this->getMigrationDependencies() .
            $this->getEndOfLine() .
            $this->getClassDefinition() .
            $this->getClosingTag();
    }

    public function getClassDefinition(): string
    {
        $migrationClassName = ucwords($this->modelService->getModelName());
        $migrationTableName = "Create{$migrationClassName}Table";
        return "class {$migrationTableName} extends Migration" . PHP_EOL . "{" . static::PHP_CRT;
    }


//    use Illuminate\Support\Facades\Schema;
//    use Illuminate\Database\Schema\Blueprint;
//    use Illuminate\Database\Migrations\Migration;
//
//class CreateJustTestingsTable extends Migration
//{
//    /**
//     * Run the migrations.
//     *
//     * @return void
//     */
//    public function up()
//    {
//        Schema::create('just_testings', function (Blueprint $table) {
//            $table->increments('id');
//            $table->timestamps();
//        });
//    }
//
//    /**
//     * Reverse the migrations.
//     *
//     * @return void
//     */
//    public function down()
//    {
//        Schema::dropIfExists('just_testings');
//    }
//}

}
