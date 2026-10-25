<?php

namespace Tests\Feature\Training\Application;

use App\Mail\Training\Skills\ApplicationProcessed;
use App\Mail\Training\Skills\ApplicationSubmitted;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\AwardedSkill;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use Database\Factories\Training\Skills\ApplicationFactory;
use Database\Factories\Training\Skills\AwardedSkillFactory;
use Database\Factories\Training\Skills\SkillFactory;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Members apply for a skill level, and admins grant it, grant a different level, or decline it.
 */

function createSkillApplication(?Skill $skill = null, int $level = 1): Application
{
    return ApplicationFactory::new()
        ->for($skill ?? SkillFactory::new()->createOne(['name' => 'Rigging']))
        ->for(createUser('member', ['username' => 'applicant', 'forename' => 'Ada', 'surname' => 'Applicant']))
        ->forLevel($level)
        ->createOne(['reasoning' => 'I have rigged lots of things']);
}

function levelIn(User $user, Skill $skill): ?int
{
    $level = AwardedSkill::where('user_id', $user->id)->where('skill_id', $skill->id)->value('level');

    return $level === null ? null : (int) $level;
}

describe('the application form', function () {
    it('should only be for members', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.skill.apply.form')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should only offer the levels the member hasn\'t got', function () {
        $skill = SkillFactory::new()->createOne();
        $member = loginAs('member');
        AwardedSkillFactory::new()
            ->for($skill)
            ->for($member ?? createUser())
            ->level(1)
            ->createOne();

        get(route('training.skill.apply.form', ['id' => $skill->id]))
            ->assertOk()
            ->assertDontSee('<option value="1">', false)
            ->assertSee('<option value="2">', false);
    });

    it('should send members back if they already have Level 3', function () {
        $skill = SkillFactory::new()->createOne();
        $member = loginAs('member');
        AwardedSkillFactory::new()
            ->for($skill)
            ->for($member ?? createUser())
            ->level(3)
            ->createOne();

        get(route('training.skill.apply.form', ['id' => $skill->id]))->assertRedirect(route('training.skill.index'));

        expectNotification('warning', 'There are no levels left to apply for');
    });

    it('should send members back if their application is pending', function () {
        $skill = SkillFactory::new()->createOne();
        $member = loginAs('member');
        ApplicationFactory::new()
            ->for($skill)
            ->for($member ?? createUser())
            ->createOne();

        get(route('training.skill.apply.form', ['id' => $skill->id]))->assertRedirect(
            route('training.skill.view', ['id' => $skill->id]),
        );

        expectNotification('warning', 'You already have an application pending for this skill');
    });
});

describe('applying for a skill', function () {
    it('should only let members apply', function (string $group, string|int $access) {
        Mail::fake();
        $skill = SkillFactory::new()->createOne();
        loginAs($group);

        expectAccess(
            post(route('training.skill.apply'), ['skill_id' => $skill->id, 'level' => '1', 'reasoning' => 'Because']),
            $access,
        );
    })->with([['guest', 'login'], ['staff', 403], ['member', 302], ['committee', 302], ['super_admin', 403]]);

    it('should store the application and email the training officer', function () {
        Mail::fake();
        $skill = SkillFactory::new()->createOne(['name' => 'Rigging']);
        $member = loginAs('member', ['forename' => 'Ada', 'surname' => 'Applicant']);
        assert($member instanceof User);

        post(route('training.skill.apply'), [
            'skill_id' => $skill->id,
            'level' => '2',
            'reasoning' => 'I can do it **alone** & safely',
        ])->assertRedirect(route('training.skill.index'));

        $application = Application::first();
        assert($application instanceof Application);
        assertDatabaseHas('training_skill_applications', [
            'id' => $application->id,
            'skill_id' => $skill->id,
            'user_id' => $member->id,
            'applied_level' => 2,
            'reasoning' => 'I can do it **alone** &amp; safely',
            'awarded_date' => null,
        ]);
        Mail::assertQueued(ApplicationSubmitted::class, function (ApplicationSubmitted $mail) use (
            $member,
            $application,
        ) {
            $mail->assertTo('training@bts-crew.com');
            $mail->assertHasReplyTo($member->email, 'Ada Applicant');
            $mail->assertHasSubject('[Backstage Website] Training Skill Application');
            $mail->assertSeeInText('Ada Applicant has applied for **Level 2** in the skill **Rigging**');
            $mail->assertSeeInHtml(route('training.skill.application.view', ['id' => $application->id]));

            return true;
        });
        expectNotification('success', 'Application submitted');
    });

    it('should validate the application', function (string $field, string $value, string $message) {
        $skill = SkillFactory::new()->levels(1, 2)->createOne();
        loginAs('member');

        post(
            route('training.skill.apply'),
            array_merge(['skill_id' => $skill->id, 'level' => '1', 'reasoning' => 'Because'], [$field => $value]),
        )->assertSessionHasErrors([$field => $message]);

        assertDatabaseCount('training_skill_applications', 0);
    })->with([
        ['level', '4', 'Please select a valid level'],
        ['level', '3', "Please select a level that's available"],
        ['reasoning', '', 'Please provide some reasoning for your application'],
    ]);

    it('should not accept an application for a level the member already has', function () {
        $skill = SkillFactory::new()->createOne();
        $member = loginAs('member');
        AwardedSkillFactory::new()
            ->for($skill)
            ->for($member ?? createUser())
            ->level(2)
            ->createOne();

        post(route('training.skill.apply'), [
            'skill_id' => $skill->id,
            'level' => '2',
            'reasoning' => 'Because',
        ])->assertSessionHasErrors(['level' => "Please choose a level you don't already have"]);
    });

    it('should not accept a second application for the same skill', function () {
        $skill = SkillFactory::new()->createOne();
        $member = loginAs('member');
        ApplicationFactory::new()
            ->for($skill)
            ->for($member ?? createUser())
            ->createOne();

        post(route('training.skill.apply'), [
            'skill_id' => $skill->id,
            'level' => '2',
            'reasoning' => 'Because',
        ])->assertSessionHasErrors(['skill_id' => 'You already have an application pending for this skill']);
    });

    it('should fail without a skill', function () {
        loginAs('member');

        post(route('training.skill.apply'), ['level' => '1', 'reasoning' => 'Because'])->assertServerError();
    });
});

describe('the list of applications', function () {
    it('should require users to log in', function () {
        get(route('training.skill.application.index'))->assertRedirect(route('auth.login'));
    });

    it('should only be for admins', function (string $group, int $status) {
        createSkillApplication();
        loginAs($group);

        get(route('training.skill.application.index'))->assertStatus($status);
    })->with([['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list the pending applications', function () {
        createSkillApplication();
        actingAs(createUser('committee'));

        get(route('training.skill.application.index'))
            ->assertOk()
            ->assertSeeText(['applicant', 'Rigging']);
    });
});

describe('viewing an application', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        $application = createSkillApplication();
        loginAs($group);

        expectAccess(get(route('training.skill.application.view', ['id' => $application->id])), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should not let applicants see their own application', function () {
        $application = createSkillApplication();
        actingAs(User::findOrFail($application->user_id));

        get(route('training.skill.application.view', ['id' => $application->id]))->assertForbidden();
    });

    it('should offer the levels the skill has', function () {
        $application = createSkillApplication(SkillFactory::new()->levels(1, 3)->createOne());
        loginAs('committee');

        get(route('training.skill.application.view', ['id' => $application->id]))
            ->assertOk()
            ->assertSee('<option value="0">Do not award</option>', false)
            ->assertSee('<option value="1" selected="selected">Level 1</option>', false)
            ->assertDontSee('<option value="2">', false)
            ->assertSee('<option value="3">Level 3</option>', false);
    });
});

describe('processing an application', function () {
    it('should only let admins process applications', function (string $group, string|int $access) {
        Mail::fake();
        $application = createSkillApplication();
        loginAs($group);

        expectAccess(
            post(route('training.skill.application.update', ['id' => $application->id]), ['awarded_level' => '1']),
            $access,
        );
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should award the level and email the applicant', function () {
        Mail::fake();
        $application = createSkillApplication(level: 2);
        $admin = loginAs('committee', ['forename' => 'Ali', 'surname' => 'Admin']);

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '2',
            'awarded_comment' => 'Well done',
        ])->assertRedirect(route('training.skill.application.index'));

        assertDatabaseHas('training_skill_applications', [
            'id' => $application->id,
            'awarded_level' => 2,
            'awarded_by' => $admin?->id,
            'awarded_comment' => 'Well done',
        ]);
        expect($application->fresh()?->awarded_date)->not->toBeNull();
        $applicant = User::findOrFail($application->user_id);
        expect(levelIn($applicant, Skill::findOrFail($application->skill_id)))->toBe(2);
        Mail::assertQueued(ApplicationProcessed::class, function (ApplicationProcessed $mail) use ($applicant) {
            $mail->assertTo($applicant->email);
            $mail->assertHasReplyTo('training@bts-crew.com');
            $mail->assertHasSubject('[Backstage Website] Your Skill Application');
            $mail->assertSeeInText('has been processed by Ali Admin');
            $mail->assertSeeInText('You have been awarded Level 2.');
            $mail->assertSeeInText('Well done');

            return true;
        });
        expectNotification('success', 'Application processed');
    });

    it('should award a different level', function () {
        Mail::fake();
        $application = createSkillApplication(level: 3);
        loginAs('committee');

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '1',
        ])->assertRedirect();

        $applicant = User::findOrFail($application->user_id);
        expect(levelIn($applicant, Skill::findOrFail($application->skill_id)))->toBe(1);
    });

    it('should lower the member\'s existing level if a lower one is awarded', function () {
        Mail::fake();
        $skill = SkillFactory::new()->createOne();
        $application = createSkillApplication($skill, 3);
        $applicant = User::findOrFail($application->user_id);
        AwardedSkillFactory::new()->for($skill)->for($applicant)->level(2)->createOne();
        loginAs('committee');

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '1',
        ])->assertRedirect();

        expect(levelIn($applicant, $skill))->toBe(1);
    })->group('known-bug', 'bug:application-lowers-level');

    it('should decline the application with a comment', function () {
        Mail::fake();
        $application = createSkillApplication();
        loginAs('committee', ['forename' => 'Ali']);

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '0',
            'awarded_comment' => 'Not yet',
        ])->assertRedirect();

        assertDatabaseHas('training_skill_applications', ['id' => $application->id, 'awarded_level' => 0]);
        assertDatabaseCount('training_awarded_skills', 0);
        Mail::assertQueued(ApplicationProcessed::class, function (ApplicationProcessed $mail) {
            $mail->assertSeeInText("Unfortunately you haven't been awarded");

            return true;
        });
    });

    it('should need a comment to decline', function () {
        $application = createSkillApplication();
        loginAs('committee');

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '0',
        ])->assertSessionHasErrors([
            'awarded_comment' => "Please provide a reason why you haven't awarded the requested level",
        ]);
    });

    it('should validate the level', function (string $level, string $message) {
        $application = createSkillApplication(SkillFactory::new()->levels(1)->createOne());
        loginAs('committee');

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => $level,
        ])->assertSessionHasErrors(['awarded_level' => $message]);
    })->with([
        ['', 'Please select the level to award'],
        ['4', 'Please select the level to award'],
        ['2', "Please select a level that's available"],
    ]);

    it('should not let admins process their own application', function () {
        $skill = SkillFactory::new()->createOne();
        $admin = createUser('committee');
        $application = ApplicationFactory::new()->for($skill)->for($admin)->createOne();
        actingAs($admin);

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '1',
        ])->assertRedirect(route('training.skill.application.index'));

        assertDatabaseHas('training_skill_applications', ['id' => $application->id, 'awarded_date' => null]);
        expectNotification('warning', "You can't review your own application");
    });

    it('should not change an application that has been processed', function () {
        $application = createSkillApplication();
        $admin = createUser('committee');
        $application->update(['awarded_level' => 1, 'awarded_by' => $admin->id, 'awarded_date' => now()]);
        loginAs('committee');

        post(route('training.skill.application.update', ['id' => $application->id]), [
            'awarded_level' => '0',
        ])->assertRedirect(route('training.skill.application.view', ['id' => $application->id]));

        assertDatabaseHas('training_skill_applications', ['id' => $application->id, 'awarded_level' => 1]);
    });
});
