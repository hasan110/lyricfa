<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrammerExplanationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grammer_explanations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('grammer_id')->unsigned();
            $table->foreign('grammer_id')->references('id')->on('grammers')->onDelete('cascade');

            $table->enum('type' , ['explain' , 'tip' , 'attention'])->default('explain');
            $table->string('title')->nullable();
            $table->longText('content');
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
        Schema::dropIfExists('grammer_explanations');
    }
}
