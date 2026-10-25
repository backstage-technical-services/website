<?php

namespace Tests\Feature\Elections\Nomination;

use App\Models\Elections\Nomination;
use Database\Factories\Elections\ElectionFactory;
use Database\Factories\Elections\NominationFactory;
use Database\Factories\Resources\IssueFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Admins add the candidates to an election, each with a PDF manifesto, which members can read. These routes don't
 * require a login.
 */

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function nominationForm(array $changes = []): array
{
    return array_merge(
        [
            'user_id' => (string) createUser('member')->id,
            'position' => '0',
            'manifesto' => UploadedFile::fake()->createWithContent('manifesto.pdf', IssueFactory::PDF),
        ],
        $changes,
    );
}

describe('adding a candidate', function () {
    it('should only let admins add candidates', function (string $group, int $status) {
        $election = ElectionFactory::new()->createOne();
        loginAs($group);

        postAjax(route('election.nominate', ['id' => $election->id]), nominationForm())->assertStatus($status);

        assertDatabaseCount('election_nominations', $status === 200 ? 1 : 0);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should add the candidate and store their manifesto', function () {
        $election = ElectionFactory::new()->createOne(['positions' => ['Chair', 'Secretary']]);
        $member = createUser('member');
        loginAs('committee');

        postAjax(
            route('election.nominate', ['id' => $election->id]),
            nominationForm([
                'user_id' => (string) $member->id,
                'position' => '1',
                'manifesto' => UploadedFile::fake()->createWithContent('my manifesto.pdf', IssueFactory::PDF),
            ]),
        )
            ->assertOk()
            ->assertExactJson(['response' => true]);

        $nomination = Nomination::firstOrFail();
        assertDatabaseHas('election_nominations', [
            'id' => $nomination->id,
            'election_id' => $election->id,
            'user_id' => $member->id,
            'position' => 1,
            'elected' => false,
        ]);
        expect(File::get(uploadPath("resources/elections/{$election->id}/{$nomination->id}.pdf")))->toBe(
            IssueFactory::PDF,
        );
        expectNotification('success', 'Nomination created');
    });

    it('should rely on MariaDB filling in whether they were elected', function () {
        $election = ElectionFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('election.nominate', ['id' => $election->id]), nominationForm())->assertOk();

        assertDatabaseHas('election_nominations', ['elected' => 0]);
    })->group('mariadb');

    it('should validate the candidate', function (string $field, mixed $value, string $message) {
        $election = ElectionFactory::new()->createOne(['positions' => ['Chair', 'Secretary']]);
        loginAs('committee');

        postAjax(route('election.nominate', ['id' => $election->id]), nominationForm([$field => $value]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['user_id', '', 'Please select a member'],
        ['user_id', '999', 'Please select a valid member'],
        ['position', '', 'Please select a position they are running for'],
        ['position', '5', 'Please select a valid position'],
        ['manifesto', null, 'Please provide their manifesto. If you had, it might be too big to upload (max of 2MB).'],
        ['manifesto', UploadedFile::fake()->create('manifesto.docx', 10), 'Only PDFs are currently supported'],
    ]);

    it('should not add someone twice for the same position', function () {
        $election = ElectionFactory::new()->createOne();
        $member = createUser('member');
        NominationFactory::new()->for($election)->for($member)->position(0)->createOne();
        loginAs('committee');

        postAjax(
            route('election.nominate', ['id' => $election->id]),
            nominationForm(['user_id' => (string) $member->id]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position' => 'They are already running for this position']);
    });

    it('should let admins add candidates after nominations have closed', function () {
        $election = ElectionFactory::new()->votingClosed()->createOne();
        loginAs('committee');

        postAjax(route('election.nominate', ['id' => $election->id]), nominationForm())->assertOk();
    });

    it('should say if the election can\'t be found', function () {
        loginAs('committee');

        postAjax(route('election.nominate', ['id' => 999]), nominationForm())
            ->assertNotFound()
            ->assertJson(['error' => "Couldn't find that election."]);
    });

    it('should only accept AJAX requests', function () {
        $election = ElectionFactory::new()->createOne();
        loginAs('committee');

        post(route('election.nominate', ['id' => $election->id]), nominationForm())->assertNotFound();
    });
});

describe('reading a manifesto', function () {
    it('should only be for members', function (string $group, int $status) {
        $nomination = NominationFactory::new()->withManifesto()->createOne();
        loginAs($group);

        get(
            route('election.manifesto', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]),
        )->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should show the PDF inline', function () {
        $nomination = NominationFactory::new()->withManifesto('the manifesto')->createOne();
        loginAs('member');

        get(route('election.manifesto', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', "inline; filename=\"{$nomination->id}.pdf\"")
            ->assertHeader('Content-Length', '13')
            ->assertContent('the manifesto');
    });

    it('should give a 404 if the manifesto is missing', function () {
        $nomination = NominationFactory::new()->createOne();
        loginAs('member');

        get(
            route('election.manifesto', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]),
        )->assertNotFound();
    });

    it('should forbid reading a candidate from another election', function () {
        $nomination = NominationFactory::new()->withManifesto()->createOne();
        $other = ElectionFactory::new()->createOne();
        loginAs('member');

        get(route('election.manifesto', ['id' => $other->id, 'nominationId' => $nomination->id]))->assertForbidden();
    });
});

describe('removing a candidate', function () {
    function electionWithCandidate(?ElectionFactory $factory = null): Nomination
    {
        $election = ($factory ?? ElectionFactory::new())->createOne();

        return NominationFactory::new()->for($election)->withManifesto()->createOne();
    }

    it('should only let admins remove candidates', function (string $group, int $status) {
        $nomination = electionWithCandidate();
        loginAs($group);

        postAjax(
            route('election.nomination.delete', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]),
        )->assertStatus($status);

        $status === 200 ? assertModelMissing($nomination) : assertModelExists($nomination);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should remove the candidate and their manifesto', function () {
        $nomination = electionWithCandidate();
        loginAs('committee');

        postAjax(
            route('election.nomination.delete', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]),
        )
            ->assertOk()
            ->assertExactJson(['response' => true]);

        expect(uploadPath("resources/elections/{$nomination->election_id}/{$nomination->id}.pdf"))->not->toBeFile();
        expectNotification('success', 'Nomination deleted');
    });

    it('should tell members that nominations are closed before checking their access', function () {
        $nomination = electionWithCandidate(ElectionFactory::new()->votingClosed());
        loginAs('member');

        postAjax(
            route('election.nomination.delete', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]),
        )
            ->assertStatus(405)
            ->assertJson(['error' => 'Nominations are closed and so cannot be deleted.']);
    });

    it('should let admins remove candidates after nominations have closed', function () {
        $nomination = electionWithCandidate(ElectionFactory::new()->votingClosed());
        loginAs('committee');

        postAjax(
            route('election.nomination.delete', ['id' => $nomination->election_id, 'nominationId' => $nomination->id]),
        )->assertOk();
    });

    it('should say if the candidate can\'t be found', function () {
        $election = ElectionFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('election.nomination.delete', ['id' => $election->id, 'nominationId' => 999]))
            ->assertNotFound()
            ->assertJson(['error' => "Couldn't find that nomination."]);
    });
});
