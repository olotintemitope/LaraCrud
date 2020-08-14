<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateStudyTable extends Migration{	/**	 * Run the migrations.	 * 	 * @return void	 */	public function up()	{		Schema::create('studies', function (Blueprint $table) {			$table->increments('id');			$table->string('firstname');			$table->string('lastname');			$table->date('dob');			$table->integer('age', 2);			$table->bigInteger('score');			$table->boolean('agreed');			$table->dateTime('admitted_at');			$table->timestamps();		});	}	/**	 * Reverse the migrations.	 * 	 * @return void	 */	public function down()	{		Schema::dropIfExists('studies');	}}