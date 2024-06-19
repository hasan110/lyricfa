<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplaceRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replace_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('find_phrase');
            $table->string('replace_phrase');
            $table->integer('priority')->default(0);
            $table->enum('apply_on' , ['persian_text' , 'english_text' , 'all'])->default('all');
            $table->boolean('last_character')->default(0);
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
        Schema::dropIfExists('replace_rules');
    }
}
