<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create user groups table
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('name');
        });

        // Create password reset tables
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });

        // Create the users table
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 30)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('forename');
            $table->string('surname');
            $table->string('nickname', 30)->nullable();
            $table->string('phone', 13)->nullable()->default(null);
            $table->text('address')->nullable()->default(null);
            $table->string('tool_colours')->nullable()->default(null);
            $table->date('dob')->nullable()->default(null);
            $table->boolean('show_email')->default(1);
            $table->boolean('show_phone')->default(0);
            $table->boolean('show_address')->default(0);
            $table->boolean('show_age')->default(0);
            $table->text('diary_preferences');
            $table->string('export_token', 100)->nullable();
            $table->unsignedInteger('user_group_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->boolean('status')->default(0);

            $table->foreign('user_group_id')
                  ->references('id')
                  ->on('user_groups')
                  ->onDelete('set null');
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
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('password_resets');
        Schema::enableForeignKeyConstraints();
    }
}
