<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_breakages_images', function (Blueprint $table) {
            $table->increments("id");
            $table->unsignedInteger('equipment_breakage_id');
            $table->string('mime');
            $table->string('extension');
            // shares created_at with the equipment_breakages table and is never updated
            $table->timestamps();

            $table->foreign('equipment_breakage_id')->references('id')->on('equipment_breakages')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_breakages_images');
        File::cleanDirectory(base_path('resources/breakages'));
    }
};
