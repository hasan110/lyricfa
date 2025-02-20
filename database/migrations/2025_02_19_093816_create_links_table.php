<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type' , ['synonym' , 'opposite' , 'homophone']);
            $table->enum('link_from_type', ['word_definition' , 'idiom_definition']);
            $table->integer('link_from_id');
            $table->enum('link_to_type', ['word_definition' , 'idiom_definition']);
            $table->integer('link_to_id');
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
        Schema::dropIfExists('links');
    }
}
