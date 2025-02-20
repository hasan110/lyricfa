<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('subtitle');
            $table->enum('mode' , ['category' , 'label']);
            $table->integer('parent_id')->nullable();
            $table->tinyInteger('is_parent')->default(0);
            $table->tinyInteger('is_public')->default(0);
            $table->tinyInteger('need_level')->default(0);
            $table->string('color')->nullable();
            $table->integer('priority')->default(0);
            $table->enum('permission_type' , ['free' , 'paid'])->default('paid');
            $table->enum('belongs_to' , ['grammers' , 'musics' , 'films' , 'word_definitions' , 'idiom_definitions'])->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('categories');
    }
}
