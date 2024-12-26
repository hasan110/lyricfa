<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTextJoinTextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('text_join_text', function (Blueprint $table) {
            $table->bigInteger('text_id')->unsigned();
            $table->foreign('text_id')->references('id')->on('texts')->onDelete('cascade');
            $table->bigInteger('text_join_id')->unsigned();
            $table->foreign('text_join_id')->references('id')->on('text_joins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('text_join_text');
    }
}
