<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateElectionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type');
            $table->text('positions');
            $table->dateTime('nominations_start');
            $table->dateTime('nominations_end');
            $table->dateTime('voting_start');
            $table->dateTime('voting_end');
            $table->dateTime('hustings_time')->nullable();
            $table->string('hustings_location')->nullable();
            $table->unsignedInteger('bathstudent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('election_nominations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('election_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('position');
            $table->boolean('elected');

            $table->foreign('election_id')->references('id')->on('elections')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('election_nominations');
        Schema::dropIfExists('elections');
        Schema::enableForeignKeyConstraints();
    }
}
