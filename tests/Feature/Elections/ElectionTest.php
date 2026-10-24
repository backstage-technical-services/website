<?php

namespace Tests\Feature\Elections;

use App\Models\Elections\Election;
use App\Models\Elections\Nomination;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;

/**
 * Create an election where voting has closed, so the results can be set.
 */
function createClosedElection(): Election
{
    return Election::create([
        'type' => 1,
        'positions' => ['Chair', 'Secretary'],
        'nominations_start' => now()->subWeeks(3),
        'nominations_end' => now()->subWeeks(2),
        'voting_start' => now()->subWeek(),
        'voting_end' => now()->subDays(2),
    ]);
}

function createNomination(Election $election, int $position): Nomination
{
    return Nomination::forceCreate([
        'election_id' => $election->id,
        'user_id' => createUser()->id,
        'position' => $position,
        'elected' => false,
    ]);
}

it('should require users to log in to view the elections', function () {
    get(route('election.index'))->assertRedirect(route('auth.login'));
});

describe('deleting an election', function () {
    beforeEach(function () {
        // Deleting an election also deletes its manifestos from resources/elections, so don't touch any real files
        File::partialMock();
    });

    it('should not allow guests', function () {
        $election = createClosedElection();
        File::shouldReceive('deleteDirectory')->never();

        postAjax(route('election.destroy', ['id' => $election->id]))->assertUnauthorized();

        assertModelExists($election);
    });

    it('should not allow members', function () {
        $election = createClosedElection();
        File::shouldReceive('deleteDirectory')->never();

        actingAs(createUser('member'));
        postAjax(route('election.destroy', ['id' => $election->id]))->assertForbidden();

        assertModelExists($election);
    });

    it('should allow admins', function () {
        $election = createClosedElection();
        File::shouldReceive('deleteDirectory')->once()->with($election->getManifestoPath());

        actingAs(createUser('committee'));
        postAjax(route('election.destroy', ['id' => $election->id]))->assertOk();

        assertModelMissing($election);
    });
});

describe('setting the elected committee', function () {
    it('should not allow guests', function () {
        $election = createClosedElection();
        $nomination = createNomination($election, 0);

        postAjax(route('election.elect', ['id' => $election->id]), [
            'elected' => [$nomination->id],
        ])->assertUnauthorized();

        expect($nomination->fresh()?->elected)->toBeFalsy();
    });

    it('should not allow members', function () {
        $election = createClosedElection();
        $nomination = createNomination($election, 0);

        actingAs(createUser('member'));
        postAjax(route('election.elect', ['id' => $election->id]), ['elected' => [$nomination->id]])->assertForbidden();

        expect($nomination->fresh()?->elected)->toBeFalsy();
    });

    it('should allow admins', function () {
        $election = createClosedElection();
        $elected = createNomination($election, 0);
        $notElected = createNomination($election, 0);

        actingAs(createUser('committee'));
        postAjax(route('election.elect', ['id' => $election->id]), ['elected' => [$elected->id]])->assertOk();

        expect($elected->fresh()?->elected)->toBeTruthy();
        expect($notElected->fresh()?->elected)->toBeFalsy();
    });
});
