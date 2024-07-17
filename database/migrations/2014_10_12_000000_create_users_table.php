<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone_number');
            $table->string('prefix_code')->default('98');
            $table->string('email')->nullable();
            $table->text('api_token')->nullable();
            $table->text('address')->nullable();
            $table->text('profile_picture')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('code_introduce')->nullable();
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
        Schema::dropIfExists('users');
    }
}
