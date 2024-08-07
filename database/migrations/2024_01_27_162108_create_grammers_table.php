<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrammersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grammers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('english_name');
            $table->string('persian_name');
            $table->text('description');
            $table->enum('level' , ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']);
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
        Schema::dropIfExists('grammers');
    }
}
