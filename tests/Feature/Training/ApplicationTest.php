<?php

namespace Tests\Feature\Training;

use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function createSkillApplication(): Application
{
    $skill = Skill::create(['name' => 'Rigging', 'description' => 'Hanging things from other things']);

    return Application::create([
        'skill_id' => $skill->id,
        'user_id' => createUser('member', ['username' => 'applicant'])->id,
        'applied_level' => 1,
        'reasoning' => 'I have rigged lots of things',
        'date' => now(),
    ]);
}

describe('viewing the list of skill applications', function () {
    it('should require users to log in', function () {
        get(route('training.skill.application.index'))->assertRedirect(route('auth.login'));
    });

    it('should not allow members', function () {
        createSkillApplication();

        actingAs(createUser('member'));
        get(route('training.skill.application.index'))->assertForbidden();
    });

    it('should allow admins', function () {
        createSkillApplication();

        actingAs(createUser('committee'));
        get(route('training.skill.application.index'))->assertOk()->assertSeeText('applicant');
    });
});
