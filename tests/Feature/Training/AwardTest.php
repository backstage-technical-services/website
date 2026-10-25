<?php

namespace Tests\Feature\Training\Award;

use App\Models\Training\Skills\AwardedSkill;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use Database\Factories\Training\Skills\ApplicationFactory;
use Database\Factories\Training\Skills\AwardedSkillFactory;
use Database\Factories\Training\Skills\SkillFactory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Admins, and members with Level 3 in a skill, can award and revoke levels of it for several members at once.
 */

function levelThreeIn(Skill $skill): User
{
    $member = createUser('member');
    AwardedSkillFactory::new()->for($skill)->for($member)->level(3)->createOne();

    return $member;
}

function levelOf(User $user, Skill $skill): ?int
{
    $level = AwardedSkill::where('user_id', $user->id)->where('skill_id', $skill->id)->value('level');

    return $level === null ? null : (int) $level;
}

describe('the award form', function () {
    it('should only be for admins without a skill', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.skill.award.form')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should let members with Level 3 award that skill', function (int $level, int $status) {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($member)->level($level)->createOne();
        actingAs($member);

        get(route('training.skill.award.form', ['id' => $skill->id]))->assertStatus($status);
    })->with([[3, 200], [2, 403]]);
});

describe('awarding a skill', function () {
    it('should only let admins and Level 3 members award', function (string $group, int $status) {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        loginAs($group);

        post(route('training.skill.award'), [
            'skill_id' => $skill->id,
            'level' => '1',
            'members' => [$member->id],
        ])->assertStatus($status);
    })->with([['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should award the level to a member, with no emails', function () {
        Mail::fake();
        Notification::fake();
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        $admin = loginAs('committee');

        post(route('training.skill.award'), [
            'skill_id' => $skill->id,
            'level' => '2',
            'members' => [$member->id],
        ])->assertRedirect(route('training.skill.index'));

        assertDatabaseHas('training_awarded_skills', [
            'skill_id' => $skill->id,
            'user_id' => $member->id,
            'level' => 2,
            'awarded_by' => $admin?->id,
        ]);
        expectNotification('success', 'Skill awarded to selected members');
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    });

    it('should let a Level 3 member award the skill', function () {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        actingAs(levelThreeIn($skill));

        post(route('training.skill.award'), [
            'skill_id' => $skill->id,
            'level' => '3',
            'members' => [$member->id],
        ])->assertRedirect(route('training.skill.index'));

        expect(levelOf($member, $skill))->toBe(3);
    });

    it('should fail when awarding several members at once', function () {
        $skill = SkillFactory::new()->createOne();
        $members = [createUser('member'), createUser('member')];
        loginAs('committee');

        post(route('training.skill.award'), [
            'skill_id' => $skill->id,
            'level' => '1',
            'members' => array_map(fn(User $member) => $member->id, $members),
        ])->assertServerError();

        assertDatabaseCount('training_awarded_skills', 0);
    })->group('known-bug', 'bug:award-several-members');

    it('should only raise a member\'s level', function () {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($member)->level(3)->createOne();
        loginAs('committee');

        post(route('training.skill.award'), [
            'skill_id' => $skill->id,
            'level' => '1',
            'members' => [$member->id],
        ])->assertRedirect();

        expect(levelOf($member, $skill))->toBe(3);
    });

    it('should not let anyone award themselves', function () {
        $skill = SkillFactory::new()->createOne();
        $admin = loginAs('committee');

        from(route('training.skill.award.form'))
            ->post(route('training.skill.award'), [
                'skill_id' => $skill->id,
                'level' => '1',
                'members' => [$admin?->id],
            ])
            ->assertRedirect(route('training.skill.award.form'));

        assertDatabaseCount('training_awarded_skills', 0);
        expectNotification('warning', "You can't award yourself a skill level");
    });

    it('should close the member\'s pending applications for that level or below', function () {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        $lower = ApplicationFactory::new()->for($skill)->for($member)->forLevel(2)->createOne();
        $higher = ApplicationFactory::new()->for($skill)->for($member)->forLevel(3)->createOne();
        $admin = loginAs('committee');

        post(route('training.skill.award'), [
            'skill_id' => $skill->id,
            'level' => '2',
            'members' => [$member->id],
        ])->assertRedirect();

        assertDatabaseHas('training_skill_applications', [
            'id' => $lower->id,
            'awarded_level' => 2,
            'awarded_by' => $admin?->id,
            'awarded_comment' => "Awarded using 'Award Skill' functionality",
        ]);
        assertDatabaseHas('training_skill_applications', ['id' => $higher->id, 'awarded_date' => null]);
    });

    it('should validate the award', function (string $field, mixed $value, string $message) {
        $skill = SkillFactory::new()->levels(1, 2)->createOne();
        loginAs('committee');

        post(
            route('training.skill.award'),
            array_merge(
                ['skill_id' => $skill->id, 'level' => '1', 'members' => [createUser()->id]],
                [$field => $value],
            ),
        )->assertSessionHasErrors([$field => $message]);
    })->with([
        ['level', '', 'Please select a level'],
        ['level', '4', 'Please select a valid level'],
        ['level', '3', "That level isn't available for the selected skill"],
        ['members', [], 'Please select at least 1 member'],
        ['members', [999], 'Please ensure all of the members are valid'],
    ]);

    it('should need a skill', function () {
        loginAs('committee');

        post(route('training.skill.award'), ['level' => '1', 'members' => [createUser()->id]])->assertServerError();
    });
});

describe('the revoke form', function () {
    it('should only be for admins without a skill', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.skill.revoke.form')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should offer to revoke completely', function () {
        $skill = SkillFactory::new()->createOne();
        loginAs('committee');

        get(route('training.skill.revoke.form', ['id' => $skill->id]))
            ->assertOk()
            ->assertSee('<option value="0">Completely revoke</option>', false);
    });
});

describe('revoking a skill', function () {
    it('should reduce the members\' levels', function () {
        $skill = SkillFactory::new()->createOne();
        $higher = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($higher)->level(3)->createOne();
        $lower = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($lower)->level(1)->createOne();
        loginAs('committee');

        post(route('training.skill.revoke'), [
            'skill_id' => $skill->id,
            'level' => '2',
            'members' => [$higher->id, $lower->id],
        ])->assertRedirect(route('training.skill.index'));

        expect(levelOf($higher, $skill))->toBe(2);
        expect(levelOf($lower, $skill))->toBe(1);
        expectNotification('success', 'Skill level reduced');
    });

    it('should not be able to revoke a skill completely', function () {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($member)->level(2)->createOne();
        loginAs('committee');

        post(route('training.skill.revoke'), [
            'skill_id' => $skill->id,
            'level' => '0',
            'members' => [$member->id],
        ])->assertSessionHasErrors(['level' => 'Please select a valid level']);

        expect(levelOf($member, $skill))->toBe(2);
    })->group('known-bug', 'bug:revoke-completely');

    it('should let Level 3 members revoke the skill', function () {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($member)->level(2)->createOne();
        actingAs(levelThreeIn($skill));

        post(route('training.skill.revoke'), [
            'skill_id' => $skill->id,
            'level' => '1',
            'members' => [$member->id],
        ])->assertRedirect(route('training.skill.index'));

        expect(levelOf($member, $skill))->toBe(1);
    });

    it('should not let anyone revoke their own skill', function () {
        $skill = SkillFactory::new()->createOne();
        $admin = loginAs('committee');
        AwardedSkillFactory::new()
            ->for($skill)
            ->for($admin ?? createUser())
            ->level(3)
            ->createOne();

        post(route('training.skill.revoke'), [
            'skill_id' => $skill->id,
            'level' => '1',
            'members' => [$admin?->id],
        ])->assertRedirect();

        expect(levelOf($admin ?? createUser(), $skill))->toBe(3);
        expectNotification('warning', "You can't revoke one of your own skills");
    });

    it('should only let admins and Level 3 members revoke', function () {
        $skill = SkillFactory::new()->createOne();
        loginAs('member');

        post(route('training.skill.revoke'), [
            'skill_id' => $skill->id,
            'level' => '1',
            'members' => [createUser()->id],
        ])->assertForbidden();
    });

    it('should log each revocation', function () {
        $skill = SkillFactory::new()->createOne();
        $member = createUser('member');
        AwardedSkillFactory::new()->for($skill)->for($member)->level(2)->createOne();
        loginAs('committee');

        post(route('training.skill.revoke'), ['skill_id' => $skill->id, 'level' => '1', 'members' => [$member->id]]);

        assertDatabaseHas('logs', ['action' => 'training-skill.revoke']);
        assertDatabaseMissing('logs', ['action' => 'training-skill.award']);
    });
});
