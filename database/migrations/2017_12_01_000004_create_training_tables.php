<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTrainingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('training_skills', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('category_id')->nullable();
            $table->text('description');
            $table->text('level1')->nullable();
            $table->text('level2')->nullable();
            $table->text('level3')->nullable();
            $table->timestamps();

            $table
                ->foreign('category_id')
                ->references('id')
                ->on('training_categories')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        Schema::create('training_awarded_skills', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('skill_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('level');
            $table->unsignedInteger('awarded_by')->nullable();
            $table->timestamps();

            $table
                ->foreign('skill_id')
                ->references('id')
                ->on('training_skills')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('awarded_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('training_skill_proposals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('skill_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('proposed_level');
            $table->text('reasoning');
            $table->dateTime('date');
            $table->unsignedInteger('awarded_level')->nullable();
            $table->unsignedInteger('awarded_by')->nullable();
            $table->text('awarded_comment')->nullable();
            $table->dateTime('awarded_date')->nullable();

            $table
                ->foreign('skill_id')
                ->references('id')
                ->on('training_skills')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('awarded_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
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
        Schema::dropIfExists('training_skill_proposals');
        Schema::dropIfExists('training_awarded_skills');
        Schema::dropIfExists('training_skills');
        Schema::dropIfExists('training_categories');
        Schema::enableForeignKeyConstraints();
    }
}
