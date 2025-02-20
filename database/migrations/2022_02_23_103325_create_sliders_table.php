<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_singer_music_album')->nullable()->unsigned();
            $table->text('ids')->default("");//id's many musics
            $table->integer('type')->nullable();// 1 is music, 2 is album, 3 is singer, 4 sum of musics
            $table->string('title')->default("");
            $table->text('description')->default("");
            $table->boolean('show_it')->default(0);
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
        Schema::dropIfExists('sliders');
    }
}
