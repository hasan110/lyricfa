<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrammerRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grammer_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('proccess_method' , ['1' , '2'])->default('1');
            $table->bigInteger('map_reason_id')->unsigned()->nullable();
            $table->foreign('map_reason_id')->references('id')->on('map_reasons')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('type')->nullable();
            $table->string('words')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grammer_rules');
    }
}
