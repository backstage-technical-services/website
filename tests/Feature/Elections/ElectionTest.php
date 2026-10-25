<?php

namespace Tests\Feature\Elections\Election;

use App\Models\Elections\Election;
use App\Models\Elections\Nomination;
use App\Models\Users\Group;
use App\Models\Users\User;
use Database\Factories\Committee\RoleFactory;
use Database\Factories\Elections\ElectionFactory;
use Database\Factories\Elections\NominationFactory;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\travelTo;
use function Pest\Laravel\withHeader;

/*
 * Committee elections. Voting happens on the SU's website: this site records the elections, candidates, manifestos
 * and results.
 */

beforeEach(function () {
    travelTo(Carbon::parse('2026-11-01 12:00:00'));
});

/**
 * The election form, as the browser sends it, with any changes.
 *
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function electionForm(array $changes = []): array
{
    return array_merge(
        [
            'type' => '1',
            'bathstudent_id' => '1234',
            'hustings_time' => '2026-11-10 18:00:00',
            'hustings_location' => 'The Plug',
            'nominations_start' => '2026-11-02 09:00:00',
            'nominations_end' => '2026-11-09 17:00:00',
            'voting_start' => '2026-11-11 09:00:00',
            'voting_end' => '2026-11-12 17:00:00',
        ],
        $changes,
    );
}

function closedElection(): Election
{
    return ElectionFactory::new()->votingClosed()->createOne();
}

function nomination(Election $election, int $position = 0): Nomination
{
    return NominationFactory::new()->for($election)->position($position)->createOne();
}

describe('the list of elections', function () {
    it('should require users to log in to view the elections', function () {
        get(route('election.index'))->assertRedirect(route('auth.login'));
    });

    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        get(route('election.index'))->assertStatus($status);
    })->with([['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should list the elections, latest voting first, 10 a page', function () {
        foreach (range(1, 11) as $i) {
            ElectionFactory::new()->createOne([
                'voting_start' => now()->subYears($i),
                'voting_end' => now()->subYears($i)->addDay(),
            ]);
        }
        loginAs('member');

        get(route('election.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['2025 By-Election', '2024 By-Election'])
            ->assertDontSeeText('2015 By-Election');
        get(route('election.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('2015 By-Election');
    });
});

describe('viewing an election', function () {
    it('should only be for members', function (string $group, string|int $access) {
        $election = ElectionFactory::new()->createOne();
        loginAs($group);

        expectAccess(get(route('election.view', ['id' => $election->id])), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should list the candidates for each position', function () {
        $election = ElectionFactory::new()->createOne(['positions' => ['Chair', 'Secretary']]);
        NominationFactory::new()
            ->for($election)
            ->for(createUser('member', ['forename' => 'Cara', 'surname' => 'Candidate']))
            ->position(1)
            ->createOne();
        loginAs('member');

        get(route('election.view', ['id' => $election->id]))
            ->assertOk()
            ->assertSeeTextInOrder(['Chair', 'Secretary', 'Cara Candidate']);
    });

    it('should link to the SU\'s voting page while voting is open', function () {
        $election = ElectionFactory::new()
            ->votingOpen()
            ->createOne(['bathstudent_id' => 1234]);
        loginAs('member');

        get(route('election.view', ['id' => $election->id]))
            ->assertOk()
            ->assertSee('href="https://www.thesubath.com/elections/vote/1234"', false);
    });

    it('should not link to the SU\'s voting page outside voting', function () {
        $election = ElectionFactory::new()->createOne(['bathstudent_id' => 1234]);
        loginAs('member');

        get(route('election.view', ['id' => $election->id]))
            ->assertOk()
            ->assertDontSee('thesubath.com', false);
    });

    it('should mark who was elected', function () {
        $election = closedElection();
        NominationFactory::new()->for($election)->elected()->createOne();
        loginAs('member');

        get(route('election.view', ['id' => $election->id]))
            ->assertOk()
            ->assertSee('title="Elected"', false);
    });
});

describe('creating an election', function () {
    it('should only let admins see the form', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('election.create')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should only let admins create elections', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(post(route('election.store'), electionForm()), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should create a full election for every committee role', function () {
        $chair = RoleFactory::new()->createOne(['name' => 'Chair', 'order' => 1]);
        $secretary = RoleFactory::new()->createOne(['name' => 'Secretary', 'order' => 2]);
        loginAs('committee');

        $response = post(route('election.store'), electionForm());

        $election = Election::firstOrFail();
        $response->assertRedirect(route('election.view', ['id' => $election->id]));
        assertDatabaseHas('elections', [
            'id' => $election->id,
            'type' => 1,
            'bathstudent_id' => 1234,
            'hustings_time' => '2026-11-10 18:00:00',
            'hustings_location' => 'The Plug',
            'nominations_start' => '2026-11-02 09:00:00',
            'nominations_end' => '2026-11-09 17:00:00',
            'voting_start' => '2026-11-11 09:00:00',
            'voting_end' => '2026-11-12 17:00:00',
        ]);
        expect($election->positions)->toBe([$chair->id => 'Chair', $secretary->id => 'Secretary']);
        expect(uploadPath("resources/elections/{$election->id}"))->toBeDirectory();
        expectNotification('success', 'Election created');
    });

    it('should create a by-election for the chosen positions', function () {
        loginAs('committee');

        post(
            route('election.store'),
            electionForm([
                'type' => '2',
                'positions' => ['Chair', 'Secretary', 'Treasurer'],
                'positions_checked' => ['0', '2'],
            ]),
        )->assertRedirect();

        expect(Election::firstOrFail()->positions)->toBe(['Chair', 'Treasurer']);
    });

    it('should shift the dates by the browser\'s offset', function () {
        loginAs('committee');

        withHeader('TZ-OFFSET', '-60')->post(route('election.store'), electionForm())->assertRedirect();

        assertDatabaseHas('elections', [
            'voting_start' => '2026-11-11 08:00:00',
            'hustings_time' => '2026-11-10 17:00:00',
        ]);
    })->group('known-bug', 'bug:timezones');

    it('should validate the election', function (string $field, mixed $value, string $message) {
        loginAs('committee');

        from(route('election.create'))
            ->post(route('election.store'), electionForm([$field => $value]))
            ->assertRedirect(route('election.create'))
            ->assertSessionHasErrors([$field => $message]);
    })->with([
        ['type', '', 'Please select an election type.'],
        ['type', '3', 'Please select a valid election type.'],
        ['bathstudent_id', 'abc', 'Please enter an integer'],
        ['hustings_time', '', 'Please enter the date of the hustings'],
        ['hustings_time', '10/11/2026 18:00', 'Please enter a valid date'],
        ['hustings_location', '', 'Please enter the hustings location'],
        ['nominations_start', '', 'Please enter when the nominations open'],
        ['nominations_end', '2026-11-01 09:00:00', "The nominations have to close after they've started!"],
        ['voting_start', '2026-11-11 09:00', 'Please enter a valid date for when voting opens'],
        ['voting_end', '2026-11-10 09:00:00', "Voting has to close after it's opened!"],
    ]);

    it('should need some positions for a by-election', function () {
        loginAs('committee');

        post(route('election.store'), electionForm(['type' => '2']))->assertSessionHasErrors([
            'positions_checked' => 'Please select at least 1 position',
        ]);
    });
});

describe('editing an election', function () {
    it('should only let admins edit elections', function (string $group, string|int $access) {
        $election = ElectionFactory::new()->createOne();
        loginAs($group);

        expectAccess(get(route('election.edit', ['id' => $election->id])), $access);
        expectAccess(
            post(route('election.update', ['id' => $election->id]), electionForm()),
            $access === 200 ? 302 : $access,
        );
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should update the election', function () {
        $election = ElectionFactory::new()->createOne();
        loginAs('committee');

        post(
            route('election.update', ['id' => $election->id]),
            electionForm([
                'type' => '2',
                'positions' => ['Chair'],
                'positions_checked' => ['0'],
                'hustings_location' => 'Online',
            ]),
        )->assertRedirect(route('election.view', ['id' => $election->id]));

        assertDatabaseHas('elections', ['id' => $election->id, 'hustings_location' => 'Online']);
        expect($election->fresh()?->positions)->toBe(['Chair']);
        expectNotification('success', 'Election updated');
    });

    it('should replace a full election\'s positions with the current committee roles', function () {
        $election = ElectionFactory::new()
            ->full([1 => 'Old Role'])
            ->votingClosed()
            ->createOne();
        $role = RoleFactory::new()->createOne(['name' => 'New Role']);
        loginAs('committee');

        post(route('election.update', ['id' => $election->id]), electionForm())->assertRedirect();

        expect($election->fresh()?->positions)->toBe([$role->id => 'New Role']);
    })->group('known-bug', 'bug:election-positions');
});

describe('deleting an election', function () {
    it('should not allow guests', function () {
        $election = closedElection();

        postAjax(route('election.destroy', ['id' => $election->id]))->assertUnauthorized();

        assertModelExists($election);
    });

    it('should only let admins delete elections', function (string $group, int $status) {
        $election = closedElection();
        loginAs($group);

        postAjax(route('election.destroy', ['id' => $election->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($election) : assertModelExists($election);
    })->with([['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the election, its candidates and their manifestos', function () {
        $election = closedElection();
        $nomination = NominationFactory::new()->for($election)->withManifesto()->createOne();
        loginAs('committee');

        postAjax(route('election.destroy', ['id' => $election->id]))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        assertModelMissing($nomination);
        expect(uploadPath("resources/elections/{$election->id}"))->not->toBeDirectory();
        expectNotification('success', 'Election deleted');
    });

    it('should say if the election can\'t be found', function () {
        loginAs('committee');

        postAjax(route('election.destroy', ['id' => 999]))
            ->assertNotFound()
            ->assertJson(['error' => "Couldn't find that election."]);
    });

    it('should only accept AJAX requests', function () {
        $election = closedElection();
        loginAs('committee');

        post(route('election.destroy', ['id' => $election->id]))->assertNotFound();

        assertModelExists($election);
    });
});

describe('setting the elected committee', function () {
    it('should not allow guests', function () {
        $election = closedElection();
        $nomination = nomination($election);

        postAjax(route('election.elect', ['id' => $election->id]), [
            'elected' => [$nomination->id],
        ])->assertUnauthorized();

        expect($nomination->fresh()?->elected)->toBeFalsy();
    });

    it('should not allow members', function () {
        $election = closedElection();
        $nomination = nomination($election);

        loginAs('member');
        postAjax(route('election.elect', ['id' => $election->id]), ['elected' => [$nomination->id]])->assertForbidden();

        expect($nomination->fresh()?->elected)->toBeFalsy();
    });

    it('should allow admins', function () {
        $election = closedElection();
        $elected = nomination($election);
        $notElected = nomination($election);

        loginAs('committee');
        postAjax(route('election.elect', ['id' => $election->id]), ['elected' => [$elected->id]])->assertOk();

        expect($elected->fresh()?->elected)->toBeTruthy();
        expect($notElected->fresh()?->elected)->toBeFalsy();
        expectNotification('success', 'Committee saved');
    });

    it('should let admins set the results before voting has closed', function () {
        $election = ElectionFactory::new()->votingOpen()->createOne();
        $nomination = nomination($election);
        loginAs('committee');

        postAjax(route('election.elect', ['id' => $election->id]), ['elected' => [$nomination->id]])->assertOk();

        expect($nomination->fresh()?->elected)->toBeTruthy();
    });

    it('should not update the committee roles or groups', function () {
        $election = ElectionFactory::new()
            ->full([1 => 'Chair'])
            ->votingClosed()
            ->createOne();
        $role = RoleFactory::new()->createOne(['name' => 'Chair']);
        $nomination = nomination($election, 1);
        loginAs('committee');

        postAjax(route('election.elect', ['id' => $election->id]), ['elected' => [$nomination->id]])->assertOk();

        assertDatabaseHas('committee_roles', ['id' => $role->id, 'user_id' => null]);
        expect(Group::find(User::findOrFail($nomination->user_id)->user_group_id)?->name)->toBe('member');
    });

    it('should say if the election can\'t be found', function () {
        loginAs('committee');

        postAjax(route('election.elect', ['id' => 999]))->assertNotFound();
    });
});
