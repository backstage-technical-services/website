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
                  ->onDelete('cascade');
            $table->foreign('award_season_id')
                  ->references('id')
                  ->on('award_seasons')
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
                  ->onDelete('cascade');
            $table->foreign('award_season_id')
                  ->references('id')
                  ->on('award_seasons')
                  ->onDelete('cascade');
            $table->foreign('suggested_by')
                  ->references('id')
                  ->on('users')
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
                  ->onDelete('cascade');
            $table->foreign('nomination_id')
                  ->references('id')
                  ->on('award_nominations')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        $this->fillAwards();
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

    private function fillAwards()
    {
        $time  = Carbon::now()->format('Y-m-d H:i:s');
        $query = DB::connection()
                   ->getPdo()
                   ->prepare('INSERT INTO `awards` (`name`, `description`, `suggested_by`, `recurring`, `created_at`, `updated_at`) VALUES (?, ?, NULL, TRUE, ?, ?)');

        $query->execute([
            'Best Lighting Design',
            'Award for the Best Lighting Design at an Event / Show.',
            $time,
            $time,
        ]);
        $query->execute([
            'Best Sound Design',
            'Award for Best Sound Design / Engineering at an Event / Show.',
            $time,
            $time,
        ]);
        $query->execute([
            'Best Set Design',
            'Award for the Best Set Design.',
            $time,
            $time,
        ]);
        $query->execute([
            'Best Lx / Noise Op',
            'Award for Best Board Operative, lighting or sound.',
            $time,
            $time,
        ]);
        $query->execute([
            'Breakage of the Year',
            'Award for the Breakage of the Year, equipment, people....',
            $time,
            $time,
        ]);
        $query->execute([
            'Most Unusual Rigging Award',
            'Most unusual rigging award, equipment, lights, sound, people...',
            $time,
            $time,
        ]);
        $query->execute([
            'Event Crew of the Year',
            'The Best crew as a whole at a single event.',
            $time,
            $time,
        ]);
        $query->execute([
            'Most Abused of the Year',
            'Most abused person or piece of kit.',
            $time,
            $time,
        ]);
        $query->execute([
            'The Matt Nutt Award for the Most Annoying Associate of the Year',
            'Pretty obvious really, to be taken light heartedly of course...',
            $time,
            $time,
        ]);
        $query->execute([
            'Procrastinator of the Year',
            'Crew Member who has avoided the most academic work by taking part in more Backstage activities than is healthy for them.',
            $time,
            $time,
        ]);
        $query->execute([
            'Quote of the Year',
            'Best quote for this year.',
            $time,
            $time,
        ]);
        $query->execute([
            'Luvvie / Twirlie of the Year',
            'For someone that seems to spend more time than they should on-stage rather than Backstage, whether they were trying to be or not...',
            $time,
            $time,
        ]);
        $query->execute([
            'Best Special Effect',
            'Video / Pyro / Water etc.',
            $time,
            $time,
        ]);
        $query->execute([
            'Fresher of the Year',
            'Next year they will be all grown up.',
            $time,
            $time,
        ]);
        $query->execute([
            'Health and Safety Offender of the Year',
            'Pretty Obvious...',
            $time,
            $time,
        ]);
        $query->execute([
            'Graduate with Honours from the Matt Wyles School of Diplomacy',
            'The person who has shown considerable diplomacy or lack of diplomacy to a comical extent.',
            $time,
            $time,
        ]);
        $query->execute([
            'The Lizze Attwood Award For Time Management',
            'The person who is considered "Best" at time management',
            $time,
            $time,
        ]);
        $query->execute([
            'Most Improved Member',
            'For the member who as improved the most over the last year.',
            $time,
            $time,
        ]);
    }
}
