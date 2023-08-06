<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMusicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('musics', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default("");
            $table->string('persian_name')->default("");
            $table->string('album')->nullable();
            $table->integer('singer')->nullable();
            $table->integer('degree')->default(0);
            $table->integer('size')->nullable();
            $table->integer('interest')->nullable();
            $table->integer('mvideo')->default(0);
            $table->integer('synchvideo')->default(0);
            $table->string('typevideo')->default("mp4");

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
        Schema::dropIfExists('musics');
    }
}
