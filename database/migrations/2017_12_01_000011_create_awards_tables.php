<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAwardsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('suggested_by')->nullable();
            $table->boolean('recurring');
            $table->timestamps();

            $table->foreign('suggested_by')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('award_seasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('status')->nullable(true);
            $table->timestamps();
        });

        Schema::create('award_award_season', function (Blueprint $table) {
            $table->unsignedInteger('award_id');
            $table->unsignedInteger('award_season_id');

            $table->foreign('award_id')
                  ->references('id')
                  ->on('awards')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('award_season_id')
                  ->references('id')
                  ->on('award_seasons')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('award_nominations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('award_id');
            $table->unsignedInteger('award_season_id');
            $table->string('nominee');
            $table->text('reason');
            $table->boolean('approved');
            $table->boolean('won');
            $table->unsignedInteger('suggested_by');
            $table->timestamps();

            $table->foreign('award_id')
                  ->references('id')
                  ->on('awards')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('award_season_id')
                  ->references('id')
                  ->on('award_seasons')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('suggested_by')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('award_votes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('award_season_id');
            $table->unsignedInteger('nomination_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('award_season_id')
                  ->references('id')
                  ->on('award_seasons')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('nomination_id')
                  ->references('id')
                  ->on('award_nominations')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
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
        Schema::dropIfExists('award_votes');
        Schema::dropIfExists('award_nominations');
        Schema::dropIfExists('award_award_season');
        Schema::dropIfExists('award_seasons');
        Schema::dropIfExists('awards');
        Schema::enableForeignKeyConstraints();
    }
}
