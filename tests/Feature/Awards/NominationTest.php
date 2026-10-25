<?php

namespace Tests\Feature\Awards\Nomination;

use App\Models\Awards\Award;
use App\Models\Awards\Season;
use Database\Factories\Awards\AwardFactory;
use Database\Factories\Awards\NominationFactory;
use Database\Factories\Awards\SeasonFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Members nominate people for the season's awards, and admins approve (or delete) the nominations before voting.
 */

function seasonWithStatus(?int $status = 1): Season
{
    return SeasonFactory::new()->status($status)->hasAttached(AwardFactory::new(), [], 'awards')->createOne();
}

function firstAward(Season $season): Award
{
    return Award::query()
        ->select('awards.*')
        ->join('award_award_season', 'awards.id', '=', 'award_award_season.award_id')
        ->where('award_award_season.award_season_id', $season->id)
        ->firstOrFail();
}

describe('nominating', function () {
    it('should only let members nominate while nominations are open', function (
        string $group,
        ?int $status,
        string|int $access,
    ) {
        $season = seasonWithStatus($status);
        loginAs($group);

        expectAccess(
            post(route('award.season.nomination.store', ['id' => $season->id]), [
                'award_id' => firstAward($season)->id,
                'nominee' => 'Nina Nominee',
                'reason' => 'Always on time',
            ]),
            $access,
        );

        assertDatabaseCount('award_nominations', $access === 302 ? 1 : 0);
    })->with([
        ['guest', 1, 'login'],
        ['staff', 1, 403],
        ['member', 1, 302],
        ['committee', 1, 302],
        ['super_admin', 1, 403],
        ['member', null, 403],
        ['member', 2, 403],
        ['member', 3, 403],
    ]);

    it('should store the nomination, unapproved', function () {
        $season = seasonWithStatus();
        $member = loginAs('member');

        post(route('award.season.nomination.store', ['id' => $season->id]), [
            'award_id' => firstAward($season)->id,
            'nominee' => 'Nina & co',
            'reason' => '<b>Always</b> on time',
        ])->assertRedirect(route('award.season.view', ['id' => $season->id]));

        assertDatabaseHas('award_nominations', [
            'award_id' => firstAward($season)->id,
            'award_season_id' => $season->id,
            'nominee' => 'Nina &amp; co',
            'reason' => 'Always on time',
            'approved' => false,
            'won' => false,
            'suggested_by' => $member?->id,
        ]);
        expectNotification('success', 'Nomination created');
    });

    it('should let members nominate as many times as they like', function () {
        $season = seasonWithStatus();
        loginAs('member');

        foreach (range(1, 3) as $i) {
            post(route('award.season.nomination.store', ['id' => $season->id]), [
                'award_id' => firstAward($season)->id,
                'nominee' => 'Nina Nominee',
                'reason' => 'Again',
            ])->assertRedirect();
        }

        assertDatabaseCount('award_nominations', 3);
    });

    it('should accept an award that isn\'t part of the season', function () {
        $season = seasonWithStatus();
        $other = AwardFactory::new()->createOne();
        loginAs('member');

        post(route('award.season.nomination.store', ['id' => $season->id]), [
            'award_id' => $other->id,
            'nominee' => 'Nina Nominee',
            'reason' => 'Because',
        ])->assertRedirect();

        assertDatabaseHas('award_nominations', ['award_id' => $other->id, 'award_season_id' => $season->id]);
    });

    it('should validate the nomination', function (string $field, string $value, string $message) {
        $season = seasonWithStatus();
        loginAs('member');

        post(
            route('award.season.nomination.store', ['id' => $season->id]),
            array_merge(
                ['award_id' => (string) firstAward($season)->id, 'nominee' => 'Nina', 'reason' => 'Why'],
                [$field => $value],
            ),
        )->assertSessionHasErrors([$field => $message]);
    })->with([
        ['award_id', '', 'Please select the award'],
        ['award_id', '999', 'Please select a valid award'],
        ['nominee', '', 'Please enter the nominee'],
        ['reason', '', 'Please enter the reason'],
    ]);

    it('should forbid nominating for an unknown season', function () {
        loginAs('member');

        post(route('award.season.nomination.store', ['id' => 999]), [
            'award_id' => '1',
            'nominee' => 'N',
            'reason' => 'R',
        ])->assertForbidden();
    });
});

describe('the list of nominations', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        $season = seasonWithStatus();
        loginAs($group);

        expectAccess(get(route('award.season.nomination.index', ['id' => $season->id])), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list every nomination with who suggested it', function () {
        $season = seasonWithStatus();
        NominationFactory::new()
            ->for($season)
            ->for(firstAward($season))
            ->createOne([
                'nominee' => 'Nina Nominee',
                'suggested_by' => createUser('member', ['forename' => 'Sue', 'surname' => 'Suggester'])->id,
            ]);
        loginAs('committee');

        get(route('award.season.nomination.index', ['id' => $season->id]))
            ->assertOk()
            ->assertSeeText(['Nina Nominee', 'Sue Suggester']);
    });
});

describe('approving a nomination', function () {
    it('should toggle whether the nomination is approved', function () {
        $season = seasonWithStatus();
        $nomination = NominationFactory::new()->for($season)->for(firstAward($season))->createOne();
        loginAs('committee');

        postAjax(route('award.season.nomination.approve', ['id' => $season->id, 'nominationId' => $nomination->id]))
            ->assertOk()
            ->assertExactJson(['response' => true]);
        assertDatabaseHas('award_nominations', ['id' => $nomination->id, 'approved' => true]);
        expectNotification('success', 'Nomination approved');

        postAjax(
            route('award.season.nomination.approve', ['id' => $season->id, 'nominationId' => $nomination->id]),
        )->assertOk();
        assertDatabaseHas('award_nominations', ['id' => $nomination->id, 'approved' => false]);
        expectNotification('success', 'Nomination unapproved');
    });

    it('should only let admins approve nominations, while nominations are open', function (
        string $group,
        ?int $status,
        int $code,
    ) {
        $season = seasonWithStatus($status);
        $nomination = NominationFactory::new()->for($season)->for(firstAward($season))->createOne();
        loginAs($group);

        postAjax(
            route('award.season.nomination.approve', ['id' => $season->id, 'nominationId' => $nomination->id]),
        )->assertStatus($code);
    })->with([
        ['guest', 1, 401],
        ['member', 1, 403],
        ['committee', 1, 200],
        ['super_admin', 1, 200],
        ['committee', 2, 403],
        ['committee', null, 403],
    ]);

    it('should give a 404 for a nomination from another season', function () {
        $season = seasonWithStatus();
        $nomination = NominationFactory::new()->createOne();
        loginAs('committee');

        postAjax(
            route('award.season.nomination.approve', ['id' => $season->id, 'nominationId' => $nomination->id]),
        )->assertNotFound();
    });
});

describe('deleting a nomination', function () {
    it('should delete the nomination while nominations are open', function (?int $status, int $code) {
        $season = seasonWithStatus($status);
        $nomination = NominationFactory::new()->for($season)->for(firstAward($season))->createOne();
        loginAs('committee');

        postAjax(
            route('award.season.nomination.destroy', ['id' => $season->id, 'nominationId' => $nomination->id]),
        )->assertStatus($code);

        $code === 200 ? assertModelMissing($nomination) : assertModelExists($nomination);
    })->with([[1, 200], [2, 403], [3, 403]]);

    it('should only let admins delete nominations', function () {
        $season = seasonWithStatus();
        $nomination = NominationFactory::new()->for($season)->for(firstAward($season))->createOne();
        loginAs('member');

        postAjax(
            route('award.season.nomination.destroy', ['id' => $season->id, 'nominationId' => $nomination->id]),
        )->assertForbidden();
    });

    it('should only accept AJAX requests', function () {
        $season = seasonWithStatus();
        $nomination = NominationFactory::new()->for($season)->for(firstAward($season))->createOne();
        loginAs('committee');

        post(
            route('award.season.nomination.destroy', ['id' => $season->id, 'nominationId' => $nomination->id]),
        )->assertNotFound();
    });
});
