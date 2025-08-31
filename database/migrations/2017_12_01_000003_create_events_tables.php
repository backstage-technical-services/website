<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('venue');
            $table->unsignedInteger('em_id')->nullable();
            $table->text('description');
            $table->unsignedInteger('type');
            $table->smallInteger('crew_list_status');
            $table->unsignedInteger('client_type')->nullable();
            $table->unsignedInteger('venue_type')->nullable();
            $table->text('paperwork');
            $table->timestamps();

            $table->foreign('em_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('event_times', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->string('name');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('event_crew', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('guest_name', 100);
            $table->string('name')->nullable();
            $table->boolean('em');
            $table->boolean('confirmed');

            $table->foreign('event_id')->references('id')->on('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('event_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('sender_id')->nullable();
            $table->string('header');
            $table->text('body');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
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
        Schema::dropIfExists('event_emails');
        Schema::dropIfExists('event_crew');
        Schema::dropIfExists('event_times');
        Schema::dropIfExists('events');
        Schema::enableForeignKeyConstraints();
    }
}
