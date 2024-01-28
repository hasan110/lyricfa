<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrammerRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grammer_rule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('grammer_id')->unsigned();
            $table->foreign('grammer_id')->references('id')->on('grammers')->onDelete('cascade');
            $table->bigInteger('grammer_rule_id')->unsigned();
            $table->foreign('grammer_rule_id')->references('id')->on('grammer_rules')->onDelete('cascade');
            $table->integer('level')->default(0);
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
        Schema::dropIfExists('grammer_rule');
    }
}
