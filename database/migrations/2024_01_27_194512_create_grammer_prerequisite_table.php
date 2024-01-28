<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrammerPrerequisiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grammer_prerequisite', function (Blueprint $table) {
            $table->bigInteger('grammer_id')->unsigned();
            $table->foreign('grammer_id')->references('id')->on('grammers')->onDelete('cascade');
            $table->bigInteger('grammer_prerequisite_id')->unsigned();
            $table->foreign('grammer_prerequisite_id')->references('id')->on('grammers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grammer_prerequisite');
    }
}
