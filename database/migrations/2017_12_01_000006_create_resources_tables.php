<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CreateResourcesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Categories
        Schema::create('resource_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('flag')->nullable();
        });

        // Tags table
        Schema::create('resource_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
        });

        // Resources table
        Schema::create('resources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('event_id')->nullable();
            $table->unsignedInteger('type')->comment = "1 = file, 2 = gdoc";
            $table->text('href')->nullable();
            $table->unsignedInteger('access')->nullable();
            $table->timestamps();

            $table->foreign('category_id')
                  ->references('id')
                  ->on('resource_categories')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });

        if(env('APP_ENV') !== 'testing') {
            DB::statement('ALTER TABLE resources ADD FULLTEXT search(title, description)');
        }

        // Tags pivot table
        Schema::create('resource_tag', function (Blueprint $table) {
            $table->unsignedInteger('resource_id');
            $table->unsignedInteger('resource_tag_id');

            $table->foreign('resource_id')
                  ->references('id')
                  ->on('resources')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('resource_tag_id')
                  ->references('id')
                  ->on('resource_tags')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        // Create the issue table
        Schema::create('resource_issues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('issue');
            $table->unsignedInteger('resource_id');
            $table->unsignedInteger('author_id')->nullable();
            $table->text('reason');
            $table->timestamps();

            $table->foreign('author_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->foreign('resource_id')
                  ->references('id')
                  ->on('resources')
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
        Schema::table('resources', function ($table) {
            $table->dropIndex('search');
        });
        Schema::dropIfExists('resource_issues');
        Schema::dropIfExists('resource_tag');
        Schema::dropIfExists('resources');
        Schema::dropIfExists('resource_tags');
        Schema::dropIfExists('resource_categories');
        File::cleanDirectory(base_path('resources/resources'));
        Schema::enableForeignKeyConstraints();
    }
}
