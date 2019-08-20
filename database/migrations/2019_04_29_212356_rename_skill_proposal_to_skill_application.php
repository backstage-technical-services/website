<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSkillProposalToSkillApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('training_skill_proposals', 'training_skill_applications');
        Schema::table('training_skill_applications', function(Blueprint $table) {
            $table->renameColumn('proposed_level','applied_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('training_skill_applications', 'training_skill_proposals');
        Schema::table('training_skill_proposals', function(Blueprint $table) {
            $table->renameColumn('applied_level','proposed_level');
        });
    }
}
