<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTextJoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('text_joins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('text_id')->unsigned();
            $table->foreign('text_id')->references('id')->on('texts')->onDelete('cascade');
            $table->morphs('joinable');
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
        Schema::dropIfExists('text_joins');
    }
}
