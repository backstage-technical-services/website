<?php

namespace Tests\Feature\Awards\Award;

use App\Models\Awards\Award;
use Database\Factories\Awards\AwardFactory;
use Database\Factories\Awards\NominationFactory;
use Database\Factories\Awards\VoteFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The catalogue of awards, which admins manage and members suggest additions to. These pages don't require a login:
 * guests get a 403 rather than a redirect to log in.
 */

describe('the list of awards', function () {
    it('should only be for admins', function (string $group, int $status) {
        loginAs($group);

        get(route('award.index'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list the suggestions before the approved awards', function () {
        AwardFactory::new()->createOne(['name' => 'Approved award']);
        AwardFactory::new()
            ->suggestedBy(createUser())
            ->createOne(['name' => 'Suggested award']);
        loginAs('committee');

        get(route('award.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Suggested award', 'Approved award']);
    });
});

describe('creating an award', function () {
    it('should only let admins create awards', function (string $group, int $status) {
        loginAs($group);

        postAjax(route('award.store'), [
            'name' => 'Best Tech',
            'description' => 'For the best',
            'recurring' => '1',
        ])->assertStatus($status);

        assertDatabaseCount('awards', $status === 200 ? 1 : 0);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should create the award', function () {
        loginAs('committee');

        postAjax(route('award.store'), [
            'name' => 'Best <i>Tech</i>',
            'description' => 'Fish & chips',
            'recurring' => '0',
        ])
            ->assertOk()
            ->assertExactJson(['response' => 'Award created']);

        assertDatabaseHas('awards', [
            'name' => 'Best Tech',
            'description' => 'Fish &amp; chips',
            'suggested_by' => null,
            'recurring' => false,
        ]);
        expectNotification('success', 'Award created');
    });

    it('should validate the award', function (string $field, string $value, string $message) {
        loginAs('committee');

        postAjax(
            route('award.store'),
            array_merge(
                ['name' => 'Best Tech', 'description' => 'For the best', 'recurring' => '1'],
                [$field => $value],
            ),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['name', '', 'Please enter the award name'],
        ['description', '', 'Please enter a description'],
        ['recurring', '', 'Please select whether this should recur every year'],
        ['recurring', 'maybe', 'Please select a valid option'],
    ]);

    it('should only accept AJAX requests', function () {
        loginAs('committee');

        post(route('award.store'), ['name' => 'Best Tech'])->assertNotFound();
    });
});

describe('changing an award', function () {
    it('should only let admins change awards', function (string $group, int $status) {
        $award = AwardFactory::new()->createOne(['name' => 'Old']);
        loginAs($group);

        postAjax(route('award.update', ['id' => $award->id]), [
            'name' => 'New',
            'description' => 'New',
            'recurring' => '1',
        ])->assertStatus($status);

        assertDatabaseHas('awards', ['id' => $award->id, 'name' => $status === 200 ? 'New' : 'Old']);
    })->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should change the award', function () {
        $award = AwardFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('award.update', ['id' => $award->id]), [
            'name' => 'Renamed',
            'description' => 'New description',
            'recurring' => '0',
        ])->assertOk();

        assertDatabaseHas('awards', ['id' => $award->id, 'name' => 'Renamed', 'recurring' => false]);
        expectNotification('success', 'Award updated');
    });
});

describe('suggesting an award', function () {
    it('should only let members suggest awards', function (string $group, int $status) {
        loginAs($group);

        postAjax(route('award.suggest'), [
            'name' => 'Best Tea Maker',
            'description' => 'For the best brews',
        ])->assertStatus($status);

        assertDatabaseCount('awards', $status === 200 ? 1 : 0);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should store the suggestion for admins to approve', function () {
        $member = loginAs('member');

        postAjax(route('award.suggest'), ['name' => 'Best Tea Maker', 'description' => 'For the best brews'])
            ->assertOk()
            ->assertExactJson(['response' => 'Award suggested']);

        assertDatabaseHas('awards', ['name' => 'Best Tea Maker', 'suggested_by' => $member?->id, 'recurring' => false]);
        expectNotification('success', 'Award suggested');
    });

    it('should validate the suggestion', function () {
        loginAs('member');

        postAjax(route('award.suggest'), ['name' => '', 'description' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description']);
    });
});

describe('approving a suggestion', function () {
    it('should only let admins approve suggestions', function (string $group, int $status) {
        $award = AwardFactory::new()->suggestedBy(createUser())->createOne();
        loginAs($group);

        postAjax(route('award.approve', ['id' => $award->id]))->assertStatus($status);

        expect(Award::findOrFail($award->id)->isApproved())->toBe($status === 200);
    })->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should forget who suggested it', function () {
        $award = AwardFactory::new()->suggestedBy(createUser())->createOne();
        loginAs('committee');

        postAjax(route('award.approve', ['id' => $award->id]))
            ->assertOk()
            ->assertExactJson(['response' => 'Award approved']);

        assertDatabaseHas('awards', ['id' => $award->id, 'suggested_by' => null]);
    });
});

describe('deleting an award', function () {
    it('should only let admins delete awards', function (string $group, int $status) {
        $award = AwardFactory::new()->createOne();
        loginAs($group);

        postAjax(route('award.destroy', ['id' => $award->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($award) : assertModelExists($award);
    })->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete all of its nominations, votes and wins', function () {
        $award = AwardFactory::new()->createOne();
        $nomination = NominationFactory::new()->for($award)->approved()->won()->createOne();
        VoteFactory::new()->forNomination($nomination)->createOne();
        loginAs('committee');

        postAjax(route('award.destroy', ['id' => $award->id]))->assertOk();

        assertDatabaseCount('award_nominations', 0);
        assertDatabaseCount('award_votes', 0);
    })->group('known-bug', 'bug:award-delete-cascades');
});
