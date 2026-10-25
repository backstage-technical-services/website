<?php

namespace Tests\Feature\Awards;

use App\Models\Awards\Award;
use App\Models\Awards\Nomination;
use App\Models\Awards\Season;
use App\Models\Awards\Vote;
use App\Models\Log;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\post;

function createVotingSeason(): Season
{
    return Season::create(['name' => 'Awards 2026', 'status' => Season::STATUS_VOTING]);
}

function createAward(?Season $season = null): Award
{
    $award = Award::create([
        'name' => 'Best Technician',
        'description' => 'For the best technician',
        'suggested_by' => null,
        'recurring' => true,
    ]);
    $season?->awards()->attach($award);

    return $award;
}

function createAwardNomination(Season $season, Award $award, bool $approved = true): Nomination
{
    return Nomination::forceCreate([
        'award_id' => $award->id,
        'award_season_id' => $season->id,
        'nominee' => 'A Technician',
        'reason' => 'For being brilliant',
        'approved' => $approved,
        'won' => false,
        'suggested_by' => createUser()->id,
    ]);
}

it('should not allow guests to vote', function () {
    $season = createVotingSeason();
    $award = createAward($season);
    $nomination = createAwardNomination($season, $award);

    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$award->id => $nomination->id],
    ])->assertForbidden();

    assertDatabaseCount('award_votes', 0);
});

it('should record a vote for an approved nomination', function (string $group) {
    $season = createVotingSeason();
    $award = createAward($season);
    $nomination = createAwardNomination($season, $award);
    $user = createUser($group);

    actingAs($user);
    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$award->id => $nomination->id],
    ])->assertRedirect();

    assertDatabaseHas('award_votes', [
        'award_season_id' => $season->id,
        'nomination_id' => $nomination->id,
        'user_id' => $user->id,
    ]);
})->with(['member', 'committee']);

it('should only count one vote for each nomination', function (string $group) {
    $season = createVotingSeason();
    $award = createAward($season);
    $otherAward = createAward($season);
    $awardNotInSeason = createAward();
    $nomination = createAwardNomination($season, $award);

    actingAs(createUser($group));
    foreach ([$award, $otherAward, $awardNotInSeason] as $votingAward) {
        post(route('award.season.vote', ['id' => $season->id]), [
            'awards' => [$votingAward->id => $nomination->id],
        ])->assertRedirect();
    }

    expect(Vote::where('nomination_id', $nomination->id)->count())->toBe(1);
})->with(['member', 'committee']);

it('should not record a vote for an award that is not part of the season', function () {
    $season = createVotingSeason();
    $awardNotInSeason = createAward();
    $nomination = createAwardNomination($season, $awardNotInSeason);

    actingAs(createUser('member'));
    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$awardNotInSeason->id => $nomination->id],
    ])->assertRedirect();

    assertDatabaseCount('award_votes', 0);
});

it('should not record a vote for an unapproved nomination', function () {
    $season = createVotingSeason();
    $award = createAward($season);
    $nomination = createAwardNomination($season, $award, approved: false);

    actingAs(createUser('member'));
    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$award->id => $nomination->id],
    ])->assertRedirect();

    assertDatabaseCount('award_votes', 0);
});

it('should only let members vote while voting is open', function (string $group, int $status, int $code) {
    $season = Season::create(['name' => 'Awards 2026', 'status' => $status]);
    $award = createAward($season);
    $nomination = createAwardNomination($season, $award);
    loginAs($group);

    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$award->id => $nomination->id],
    ])->assertStatus($code);
})->with([['staff', 2, 403], ['member', 2, 302], ['super_admin', 2, 403], ['member', 1, 403], ['member', 3, 403]]);

it('should record a vote for each award at once', function () {
    $season = createVotingSeason();
    $first = createAward($season);
    $second = createAward($season);
    $firstNomination = createAwardNomination($season, $first);
    $secondNomination = createAwardNomination($season, $second);
    loginAs('member');

    from(route('award.season.view', ['id' => $season->id]))
        ->post(route('award.season.vote', ['id' => $season->id]), [
            'awards' => [$first->id => $firstNomination->id, $second->id => $secondNomination->id],
        ])
        ->assertRedirect(route('award.season.view', ['id' => $season->id]));

    assertDatabaseCount('award_votes', 2);
    expectNotification('success', 'Votes recorded');
});

it('should say when only some of the votes were recorded', function () {
    $season = createVotingSeason();
    $award = createAward($season);
    $nomination = createAwardNomination($season, $award);
    $unapproved = createAwardNomination($season, createAward($season), approved: false);
    loginAs('member');

    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$award->id => $nomination->id, $unapproved->award_id => $unapproved->id],
    ])->assertRedirect();

    expectNotification('warning', 'Only some votes were recorded');
});

it('should not let members change their vote', function () {
    $season = createVotingSeason();
    $award = createAward($season);
    $first = createAwardNomination($season, $award);
    $second = createAwardNomination($season, $award);
    loginAs('member');

    post(route('award.season.vote', ['id' => $season->id]), ['awards' => [$award->id => $first->id]]);
    post(route('award.season.vote', ['id' => $season->id]), [
        'awards' => [$award->id => $second->id],
    ])->assertRedirect();

    assertDatabaseCount('award_votes', 1);
    assertDatabaseHas('award_votes', ['nomination_id' => $first->id]);
    expectNotification('error', 'Uh oh! Something went wrong.');
});

it('should ask members to choose some nominations', function () {
    $season = createVotingSeason();
    loginAs('member');

    post(route('award.season.vote', ['id' => $season->id]), [])->assertRedirect();

    expectNotification('warning', 'Please select some nominations to vote for');
});

it('should log who voted for which nomination', function () {
    $season = createVotingSeason();
    $award = createAward($season);
    $nomination = createAwardNomination($season, $award);
    $member = loginAs('member');

    post(route('award.season.vote', ['id' => $season->id]), ['awards' => [$award->id => $nomination->id]]);

    $log = Log::where('action', 'award.vote')->latest('id')->first();
    expect($log?->payload)->toMatchArray(['nomination_id' => $nomination->id, 'user_id' => $member?->id]);
})->group('known-bug', 'bug:ballots-logged');
