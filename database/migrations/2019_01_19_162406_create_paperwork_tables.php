<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaperworkTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Possible Paperwork
        Schema::create('event_paperwork', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('template_link')->nullable();
            $table->timestamps();
        });

        // Paperwork pivot table
        Schema::create('event_event_paperwork', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('paperwork_id');
            $table->boolean('completed');
            $table->text('link')->nullable();
            $table->timestamps();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('paperwork_id')
                  ->references('id')
                  ->on('event_paperwork')
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
        Schema::dropIfExists('event_paperwork');
        Schema::dropIfExists('event_event_paperwork');
        Schema::enableForeignKeyConstraints();
    }
}
