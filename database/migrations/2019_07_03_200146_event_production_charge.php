<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EventProductionCharge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function(Blueprint $table) {
            $table->integer('production_charge')
                  ->after('venue_type')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function(Blueprint $table) {
            $table->dropColumn('production_charge');
        });
    }
}
