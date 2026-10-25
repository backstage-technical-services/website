<?php

namespace Tests\Feature\Training\Skill;

use App\Models\Training\Skills\Skill;
use Database\Factories\Training\CategoryFactory;
use Database\Factories\Training\Skills\AwardedSkillFactory;
use Database\Factories\Training\Skills\SkillFactory;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\travelTo;

/*
 * Training records have been kept in a Google Sheet since 2023, but the website's training section still works.
 */

/**
 * The skill form, as the browser sends it, with any changes.
 *
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function skillForm(array $changes = []): array
{
    return array_merge(
        [
            'name' => 'Rigging',
            'category_id' => '',
            'description' => 'Hanging *things*',
            'available' => ['level1' => '1', 'level2' => '1', 'level3' => '1'],
            'level1' => 'Supervised',
            'level2' => 'Unsupervised',
            'level3' => 'Teaching',
        ],
        $changes,
    );
}

describe('the list of skills', function () {
    it('should be for members and SU staff', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.skill.index')), $access);
    })->with([
        ['guest', 'login'],
        ['staff', 200],
        ['member', 200],
        ['associate', 200],
        ['committee', 200],
        ['super_admin', 403],
    ]);

    it('should redirect /training to the list of skills', function () {
        get(route('training.dash'))->assertRedirect(route('training.skill.index'));
    });

    it('should list the skills in each category, then the uncategorised ones', function () {
        $lighting = CategoryFactory::new()->createOne(['name' => 'Lighting']);
        CategoryFactory::new()->createOne(['name' => 'Audio']);
        SkillFactory::new()
            ->for($lighting)
            ->createOne(['name' => 'Rigging']);
        SkillFactory::new()->createOne(['name' => 'Ladders']);
        loginAs('member');

        get(route('training.skill.index'))
            ->assertOk()
            ->assertSeeInOrder(['Audio', 'Lighting', 'Uncategorised'])
            ->assertSeeText(['Rigging', 'Ladders']);
    });

    it('should point members to the skills matrix', function () {
        loginAs('member');

        get(route('training.skill.index'))
            ->assertOk()
            ->assertSee('https://docs.google.com/spreadsheets/d/1Xq3JVDMIE52_0iWZiymMsDvMINEfz0p0G0x2WdImSHo', false);
    });

    it('should show the buttons each group can use', function (string $group, array $buttons) {
        loginAs($group);

        $response = get(route('training.skill.index'))->assertOk();
        foreach (['Create Skill', 'Apply for Skill', 'Award Skill', 'Revoke Skill'] as $button) {
            in_array($button, $buttons) ? $response->assertSeeText($button) : $response->assertDontSeeText($button);
        }
    })->with([
        ['staff', []],
        ['member', ['Apply for Skill']],
        ['committee', ['Create Skill', 'Apply for Skill', 'Award Skill', 'Revoke Skill']],
    ]);
});

describe('viewing a skill', function () {
    it('should be for members and SU staff', function (string $group, string|int $access) {
        $skill = SkillFactory::new()->createOne();
        loginAs($group);

        expectAccess(get(route('training.skill.view', ['id' => $skill->id])), $access);
    })->with([['guest', 'login'], ['staff', 200], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should show the description and the requirements for each level', function () {
        $skill = SkillFactory::new()
            ->levels(1, 3)
            ->createOne(['description' => 'Hanging *things*', 'level1' => 'With **help**', 'level3' => 'Teaching']);
        loginAs('member');

        get(route('training.skill.view', ['id' => $skill->id]))
            ->assertOk()
            ->assertSee('Hanging <em>things</em>', false)
            ->assertSee('With <strong>help</strong>', false)
            ->assertSeeText(['Level not available', 'Teaching']);
    });

    it('should list the active members at each level', function () {
        $skill = SkillFactory::new()->createOne();
        AwardedSkillFactory::new()
            ->for($skill)
            ->for(createUser('member', ['forename' => 'Lou', 'surname' => 'Three']))
            ->level(3)
            ->createOne();
        AwardedSkillFactory::new()
            ->for($skill)
            ->for(createUser('member', ['forename' => 'Uno', 'surname' => 'One']))
            ->level(1)
            ->createOne();
        AwardedSkillFactory::new()
            ->for($skill)
            ->for(createUser('archived', ['forename' => 'Gone', 'surname' => 'Away']))
            ->level(2)
            ->createOne();
        AwardedSkillFactory::new()
            ->for($skill)
            ->for(createUser('staff', ['forename' => 'Su', 'surname' => 'Staff']))
            ->level(2)
            ->createOne();
        loginAs('member');

        get(route('training.skill.view', ['id' => $skill->id]))
            ->assertOk()
            ->assertSeeTextInOrder(['Lou Three', 'no members', 'Uno One'])
            ->assertDontSeeText(['Gone Away', 'Su Staff']);
    });

    it('should only offer to apply to members below Level 3', function (int $level, bool $offered) {
        $skill = SkillFactory::new()->createOne();
        $member = loginAs('member');
        if ($level > 0) {
            AwardedSkillFactory::new()
                ->for($skill)
                ->for($member ?? createUser())
                ->level($level)
                ->createOne();
        }

        $response = get(route('training.skill.view', ['id' => $skill->id]))->assertOk();
        $link = 'href="' . route('training.skill.apply.form', ['id' => $skill->id]) . '"';
        $offered ? $response->assertSee($link, false) : $response->assertDontSee($link, false);
    })->with([[0, true], [2, true], [3, false]]);

    it('should give a 404 for an unknown skill', function () {
        loginAs('member');

        get(route('training.skill.view', ['id' => 999]))->assertNotFound();
    });
});

describe('creating a skill', function () {
    it('should only let admins see the form', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.skill.create')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should only let admins create skills', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(post(route('training.skill.store'), skillForm()), $access);

        assertDatabaseCount('training_skills', $access === 302 ? 1 : 0);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should create the skill', function () {
        $category = CategoryFactory::new()->createOne();
        loginAs('committee');

        $response = post(
            route('training.skill.store'),
            skillForm([
                'name' => 'Rigging & Flying',
                'category_id' => (string) $category->id,
                'available' => ['level1' => '1', 'level3' => '1'],
            ]),
        );

        $skill = Skill::first();
        assert($skill instanceof Skill);
        $response->assertRedirect(route('training.skill.view', ['id' => $skill->id]));
        assertDatabaseHas('training_skills', [
            'id' => $skill->id,
            'name' => 'Rigging &amp; Flying',
            'category_id' => $category->id,
            'description' => 'Hanging *things*',
            'level1' => 'Supervised',
            'level2' => null,
            'level3' => 'Teaching',
        ]);
        expectNotification('success', 'Skill created');
    });

    it('should validate the skill', function (string $field, mixed $value, string $message) {
        loginAs('committee');

        from(route('training.skill.create'))
            ->post(route('training.skill.store'), skillForm([$field => $value]))
            ->assertRedirect(route('training.skill.create'))
            ->assertSessionHasErrors([$field => $message]);
    })->with([
        ['name', '', 'Please enter the skill name'],
        ['description', '', 'Please enter a description of the skill'],
        ['category_id', '999', 'Please choose a valid category'],
        ['level2', '', 'Please enter the requirements for Level 2'],
    ]);

    it('should not need the requirements for levels that aren\'t offered', function () {
        loginAs('committee');

        post(
            route('training.skill.store'),
            skillForm(['available' => ['level1' => '1'], 'level2' => '', 'level3' => '']),
        )->assertSessionHasNoErrors();
    });

    it('should fail if no levels are offered', function () {
        loginAs('committee');

        post(route('training.skill.store'), skillForm(['available' => null]))->assertServerError();

        assertDatabaseCount('training_skills', 0);
    })->group('known-bug', 'bug:skill-without-levels');
});

describe('editing a skill', function () {
    it('should only let admins see the form', function (string $group, string|int $access) {
        $skill = SkillFactory::new()->createOne();
        loginAs($group);

        expectAccess(get(route('training.skill.edit', ['id' => $skill->id])), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should only let admins change skills', function (string $group, string|int $access) {
        $skill = SkillFactory::new()->createOne(['name' => 'Old']);
        loginAs($group);

        expectAccess(post(route('training.skill.update', ['id' => $skill->id]), skillForm(['name' => 'New'])), $access);

        assertDatabaseHas('training_skills', ['id' => $skill->id, 'name' => $access === 302 ? 'New' : 'Old']);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should change the skill', function () {
        $skill = SkillFactory::new()->inCategory()->createOne();
        loginAs('committee');

        post(
            route('training.skill.update', ['id' => $skill->id]),
            skillForm(['name' => 'Flying', 'available' => ['level2' => '1'], 'level2' => 'New requirements']),
        )->assertRedirect(route('training.skill.view', ['id' => $skill->id]));

        assertDatabaseHas('training_skills', [
            'id' => $skill->id,
            'name' => 'Flying',
            'category_id' => null,
            'level1' => null,
            'level2' => 'New requirements',
            'level3' => null,
        ]);
        expectNotification('success', 'Skill updated');
    });

    it('should keep the members\' levels when a level stops being offered', function () {
        $skill = SkillFactory::new()->createOne();
        $awarded = AwardedSkillFactory::new()->for($skill)->level(3)->createOne();
        loginAs('committee');

        post(
            route('training.skill.update', ['id' => $skill->id]),
            skillForm(['available' => ['level1' => '1']]),
        )->assertRedirect();

        assertDatabaseHas('training_awarded_skills', ['id' => $awarded->id, 'level' => 3]);
    });

    it('should forbid changing an unknown skill', function () {
        loginAs('committee');

        post(route('training.skill.update', ['id' => 999]), skillForm())->assertForbidden();
    });
});

describe('deleting a skill', function () {
    it('should only let admins delete skills', function (string $group, int $status) {
        $skill = SkillFactory::new()->createOne();
        loginAs($group);

        postAjax(route('training.skill.destroy', ['id' => $skill->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($skill) : assertModelExists($skill);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the skill with its awards and applications', function () {
        $skill = SkillFactory::new()->createOne();
        AwardedSkillFactory::new()->for($skill)->createOne();
        loginAs('committee');

        postAjax(route('training.skill.destroy', ['id' => $skill->id]))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        assertDatabaseCount('training_awarded_skills', 0);
        expectNotification('success', 'Skill deleted');
    });

    it('should only accept AJAX requests', function () {
        $skill = SkillFactory::new()->createOne();
        loginAs('committee');

        post(route('training.skill.destroy', ['id' => $skill->id]))->assertNotFound();
    });
});

describe('the skills log', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.skill.log')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list the awarded skills, most recently changed first, 20 a page', function () {
        foreach (range(1, 21) as $i) {
            travelTo(Carbon::parse('2026-01-01')->addDays($i));
            AwardedSkillFactory::new()
                ->for(SkillFactory::new()->state(['name' => sprintf('Skill %02d', $i)]))
                ->createOne();
        }
        loginAs('committee');

        get(route('training.skill.log'))
            ->assertOk()
            ->assertSeeTextInOrder(['Skill 21', 'Skill 20'])
            ->assertDontSeeText('Skill 01');
        get(route('training.skill.log', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Skill 01');
    });
});
