<?php

namespace Tests\Feature\Awards\Season;

use App\Models\Awards\Award;
use App\Models\Awards\Season;
use Database\Factories\Awards\AwardFactory;
use Database\Factories\Awards\NominationFactory;
use Database\Factories\Awards\SeasonFactory;
use Database\Factories\Awards\VoteFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;

/*
 * Award seasons go through Closed (NULL), Nominations open (1), Voting open (2) and Results released (3), in whatever
 * order an admin sets them.
 */

function seasonWithAward(?int $status = null): Season
{
    return SeasonFactory::new()
        ->status($status)
        ->hasAttached(AwardFactory::new()->state(['name' => 'Best Tech']), [], 'awards')
        ->createOne(['name' => 'Awards 2026']);
}

function seasonAward(Season $season): Award
{
    return Award::query()
        ->select('awards.*')
        ->join('award_award_season', 'awards.id', '=', 'award_award_season.award_id')
        ->where('award_award_season.award_season_id', $season->id)
        ->firstOrFail();
}

describe('the list of seasons', function () {
    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        get(route('award.season.index'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should list the seasons, newest first', function () {
        SeasonFactory::new()->createOne(['name' => 'Awards 2025', 'created_at' => now()->subYear()]);
        SeasonFactory::new()->createOne(['name' => 'Awards 2026']);
        loginAs('member');

        get(route('award.season.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Awards 2026', 'Awards 2025']);
    });
});

describe('viewing a season', function () {
    it('should only be for members', function (string $group, int $status) {
        $season = seasonWithAward(Season::STATUS_NOMINATIONS);
        loginAs($group);

        get(route('award.season.view', ['id' => $season->id]))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should send members back to the list if the season is closed', function () {
        $season = seasonWithAward();
        loginAs('member');

        get(route('award.season.view', ['id' => $season->id]))->assertRedirect(route('award.season.index'));
    });

    it('should show admins a closed season', function () {
        $season = seasonWithAward();
        loginAs('committee');

        get(route('award.season.view', ['id' => $season->id]))
            ->assertOk()
            ->assertSeeText('This award is currently closed');
    });

    it('should show the nominations form while nominations are open', function () {
        $season = seasonWithAward(Season::STATUS_NOMINATIONS);
        loginAs('member');

        get(route('award.season.view', ['id' => $season->id]))
            ->assertOk()
            ->assertSee(route('award.season.nomination.store', ['id' => $season->id]), false)
            ->assertSeeText('Best Tech');
    });

    it('should show the approved nominations to vote for while voting is open', function () {
        $season = seasonWithAward(Season::STATUS_VOTING);
        $award = seasonAward($season);
        $approved = NominationFactory::new()
            ->for($season)
            ->for($award)
            ->approved()
            ->createOne(['nominee' => 'Approved Person']);
        NominationFactory::new()
            ->for($season)
            ->for($award)
            ->createOne(['nominee' => 'Unapproved Person']);
        loginAs('member');

        get(route('award.season.view', ['id' => $season->id]))
            ->assertOk()
            ->assertSee('name="awards[' . $award->id . ']" type="radio" value="' . $approved->id . '"', false)
            ->assertSeeText('Approved Person')
            ->assertDontSeeText('Unapproved Person');
    });

    it('should show the winners, and the vote counts to admins, once the results are released', function () {
        $season = seasonWithAward(Season::STATUS_RESULTS);
        $award = seasonAward($season);
        $winner = NominationFactory::new()
            ->for($season)
            ->for($award)
            ->approved()
            ->won()
            ->createOne(['nominee' => 'Winner']);
        VoteFactory::new()->forNomination($winner)->count(3)->create();
        loginAs('member');

        get(route('award.season.view', ['id' => $season->id]))
            ->assertOk()
            ->assertSee('title="This nomination won"', false)
            ->assertDontSee('title="3 votes"', false);

        loginAs('committee');
        get(route('award.season.view', ['id' => $season->id]))
            ->assertOk()
            ->assertSee('title="3 votes"', false);
    });

    it('should show the reasons without escaping them', function () {
        $season = seasonWithAward(Season::STATUS_RESULTS);
        NominationFactory::new()
            ->for($season)
            ->for(seasonAward($season))
            ->approved()
            ->createOne(['reason' => '<b>Raw</b> HTML']);
        loginAs('member');

        get(route('award.season.view', ['id' => $season->id]))
            ->assertOk()
            ->assertSee('<b>Raw</b> HTML', false);
    })->group('known-bug', 'bug:nomination-reason-unescaped');
});

describe('creating a season', function () {
    it('should only let admins create seasons', function (string $group, int $status) {
        $award = AwardFactory::new()->createOne();
        loginAs($group);

        postAjax(route('award.season.store'), ['name' => 'Awards 2027', 'awards' => [$award->id]])->assertStatus(
            $status,
        );

        assertDatabaseCount('award_seasons', $status === 200 ? 1 : 0);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should create the season with its awards', function () {
        $awards = AwardFactory::new()->count(2)->create();
        loginAs('committee');

        postAjax(route('award.season.store'), [
            'name' => 'Awards & Prizes',
            'status' => '1',
            'awards' => $awards->pluck('id')->all(),
        ])
            ->assertOk()
            ->assertExactJson(['response' => 'Award season created']);

        $season = Season::firstOrFail();
        assertDatabaseHas('award_seasons', ['id' => $season->id, 'name' => 'Awards &amp; Prizes', 'status' => 1]);
        expect($season->awards)->toEqualCanonicalizing($awards->pluck('id')->all());
        expectNotification('success', 'Award season created');
    });

    it('should validate the season', function (string $field, mixed $value, string $message) {
        $award = AwardFactory::new()->createOne();
        loginAs('committee');

        postAjax(
            route('award.season.store'),
            array_merge(['name' => 'Awards 2027', 'awards' => [$award->id]], [$field => $value]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['name', '', 'Please enter the award season name'],
        ['awards', [], 'Please select which awards to include in this season'],
        ['awards', [999], 'Please select a valid award'],
        ['status', '4', 'The selected status is invalid.'],
    ]);
});

describe('changing a season', function () {
    it('should rename the season and set its status', function () {
        $season = seasonWithAward();
        loginAs('committee');

        postAjax(route('award.season.update', ['id' => $season->id]), [
            'name' => 'Renamed',
            'status' => '2',
            'awards' => $season->awards,
        ])->assertOk();

        assertDatabaseHas('award_seasons', ['id' => $season->id, 'name' => 'Renamed', 'status' => 2]);
    });

    it('should ignore changes to the awards', function () {
        $season = seasonWithAward();
        $other = AwardFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('award.season.update', ['id' => $season->id]), [
            'name' => 'Awards 2026',
            'awards' => [$other->id],
        ])->assertOk();

        expect($season->fresh()?->awards)->not->toContain($other->id);
    })->group('known-bug', 'bug:season-awards-not-saved');

    it('should only let admins change seasons', function (string $group, int $status) {
        $season = seasonWithAward();
        loginAs($group);

        postAjax(route('award.season.update', ['id' => $season->id]), [
            'name' => 'Renamed',
            'awards' => $season->awards,
        ])->assertStatus($status);
    })->with([['guest', 403], ['member', 403], ['committee', 200]]);
});

describe('setting the status', function () {
    it('should only let admins set the status', function (string $group, int $status) {
        $season = seasonWithAward();
        loginAs($group);

        postAjax(route('award.season.status', ['id' => $season->id]), ['status' => '1'])->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should set any status, in any order', function (?int $from, string $to, int $stored) {
        $season = seasonWithAward($from);
        loginAs('committee');

        postAjax(route('award.season.status', ['id' => $season->id]), ['status' => $to])
            ->assertOk()
            ->assertExactJson(['response' => true]);

        assertDatabaseHas('award_seasons', ['id' => $season->id, 'status' => $stored]);
        expectNotification('success', 'Status updated');
    })->with([[null, '1', 1], [null, '3', 3], [3, '1', 1], [1, '2', 2]]);

    it('should close the season when the current status is chosen again', function () {
        $season = seasonWithAward(Season::STATUS_VOTING);
        loginAs('committee');

        postAjax(route('award.season.status', ['id' => $season->id]), ['status' => '2'])->assertOk();

        assertDatabaseHas('award_seasons', ['id' => $season->id, 'status' => null]);
    })->group('known-bug', 'bug:season-status-toggle');

    it('should reject an unknown status', function () {
        $season = seasonWithAward();
        loginAs('committee');

        postAjax(route('award.season.status', ['id' => $season->id]), ['status' => '5'])
            ->assertUnprocessable()
            ->assertJson(['error' => 'Please select a valid status']);
    });

    it('should delete every vote when going back to nominations', function (int $from) {
        $season = seasonWithAward($from);
        $nomination = NominationFactory::new()->for($season)->for(seasonAward($season))->approved()->createOne();
        VoteFactory::new()->forNomination($nomination)->count(2)->create();
        loginAs('committee');

        postAjax(route('award.season.status', ['id' => $season->id]), ['status' => '1'])->assertOk();

        assertDatabaseCount('award_votes', 0);
    })
        ->with(['voting' => 2, 'results' => 3])
        ->group('known-bug', 'bug:season-votes-wiped');

    it('should work out the winners when the results are released', function () {
        $season = seasonWithAward(Season::STATUS_VOTING);
        $award = seasonAward($season);
        $tied = AwardFactory::new()->createOne();
        $empty = AwardFactory::new()->createOne();
        $season->awards()->attach([$tied->id, $empty->id]);
        $winner = NominationFactory::new()->for($season)->for($award)->approved()->createOne();
        $loser = NominationFactory::new()->for($season)->for($award)->approved()->createOne();
        $jointA = NominationFactory::new()->for($season)->for($tied)->approved()->createOne();
        $jointB = NominationFactory::new()->for($season)->for($tied)->approved()->createOne();
        $noVotes = NominationFactory::new()->for($season)->for($empty)->approved()->won()->createOne();
        VoteFactory::new()->forNomination($winner)->count(2)->create();
        VoteFactory::new()->forNomination($loser)->createOne();
        VoteFactory::new()->forNomination($jointA)->createOne();
        VoteFactory::new()->forNomination($jointB)->createOne();
        loginAs('committee');

        postAjax(route('award.season.status', ['id' => $season->id]), ['status' => '3'])->assertOk();

        foreach (
            [[$winner, true], [$loser, false], [$jointA, true], [$jointB, true], [$noVotes, false]]
            as [$nomination, $won]
        ) {
            assertDatabaseHas('award_nominations', ['id' => $nomination->id, 'won' => $won]);
        }
    })->group('mariadb');
});

describe('deleting a season', function () {
    it('should only let admins delete seasons', function (string $group, int $status) {
        $season = seasonWithAward();
        loginAs($group);

        postAjax(route('award.season.destroy', ['id' => $season->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($season) : assertModelExists($season);
    })->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the season\'s nominations and votes, but keep the awards', function () {
        $season = seasonWithAward(Season::STATUS_VOTING);
        $nomination = NominationFactory::new()->for($season)->for(seasonAward($season))->createOne();
        VoteFactory::new()->forNomination($nomination)->createOne();
        loginAs('committee');

        postAjax(route('award.season.destroy', ['id' => $season->id]))->assertOk();

        assertDatabaseCount('award_nominations', 0);
        assertDatabaseCount('award_votes', 0);
        assertDatabaseCount('awards', 1);
    });
});
