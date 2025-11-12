<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pages');
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->boolean('published');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
