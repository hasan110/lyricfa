<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrammerExamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grammer_examples', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('grammer_explanation_id')->unsigned();
            $table->foreign('grammer_explanation_id')->references('id')->on('grammer_explanations')->onDelete('cascade');

            $table->longText('english_content');
            $table->longText('persian_content');
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
        Schema::dropIfExists('grammer_examples');
    }
}
