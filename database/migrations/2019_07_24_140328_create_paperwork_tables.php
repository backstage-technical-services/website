<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Events\Paperwork;

class CreatePaperworkTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // --- Table Creation ---
        // Possible Paperwork
        Schema::create('event_paperwork', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
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

            // Foreign key checks
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

        // --- Data Migration ---
        // Non destructively convert JSON paperwork info from events DB table into new pivot table

        // Local arrays used to reduce DB read and writes
        $knownPaperwork[] = "";     // Empty entry to match database index
        $temp_event_event_paperwork = [];

        foreach(DB::table('events')->get() as $event) {
            foreach(json_decode($event->paperwork) as $PaperworkType => $completed) {

                // Add new paperwork type to table
                if (!in_array($PaperworkType, $knownPaperwork)) {
                    Paperwork::create(['name' => $PaperworkType]);
                    $knownPaperwork[] = $PaperworkType;
                }

                // Add paperwork to local array
                $temp_event_event_paperwork[] = [
                    'event_id'      => $event->id,
                    'paperwork_id'  => array_search($PaperworkType, $knownPaperwork),
                    'completed'     => $completed ];
            }
            // Batch process to reduce DB writes
            if(sizeof($temp_event_event_paperwork)>2000) {
                DB::table('event_event_paperwork')->insert($temp_event_event_paperwork);
                $temp_event_event_paperwork = [];
            }
        }

        // --- Add Paperwork Info ---
        Paperwork::where('name', 'risk_assessment') ->update(['name' => 'Risk Assessment',
                                                        'template_link' => env('LINK_EVENT_RA')]);
        Paperwork::where('name', 'insurance')       ->update(['name' => 'Insurance']);
        Paperwork::where('name', 'finance_em')      ->update(['name' => 'TEM Finance']);
        Paperwork::where('name', 'finance_treas')   ->update(['name' => 'Treasurer Finance']);
        Paperwork::where('name', 'event_report')    ->update(['name' => 'Event Report',
                                                        'template_link' => env('LINK_EVENT_REPORT')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('event_event_paperwork');
        Schema::dropIfExists('event_paperwork');
        Schema::enableForeignKeyConstraints();
    }
}
