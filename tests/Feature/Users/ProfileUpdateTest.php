<?php

namespace Tests\Feature\Users\ProfileUpdate;

use App\Models\Log;
use App\Models\Users\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

/*
 * Members edit their own profile in modals, which all post to one endpoint with an "update" field saying what to change.
 */

/**
 * @param array<string, mixed> $data
 *
 * @return TestResponse<Response>
 */
function updateProfile(array $data): TestResponse
{
    return postAjax(route('member.update'), $data);
}

function avatarPath(User $user): string
{
    return public_path("images/profiles/{$user->username}.jpg");
}

it('should need a login', function () {
    updateProfile(['update' => 'personal', 'name' => 'New Name'])->assertUnauthorized();
});

it('should only accept AJAX requests', function () {
    loginAs('member');

    post(route('member.update'), ['update' => 'personal', 'name' => 'New Name'])->assertNotFound();
});

it('should reject an unknown change', function () {
    loginAs('member');

    updateProfile(['update' => 'everything'])
        ->assertNotFound()
        ->assertJson(['error' => 'Unknown action', 'error_code' => 404]);
});

describe('personal details', function () {
    it('should let anyone who is logged in change their own', function (string $group) {
        $user = loginAs($group);
        assert($user instanceof User);

        updateProfile(['update' => 'personal', 'name' => 'Jo Bloggs', 'nickname' => 'Jojo', 'dob' => '2001-02-03'])
            ->assertOk()
            ->assertExactJson(['response' => 'Changes saved']);

        assertDatabaseHas('users', [
            'id' => $user->id,
            'forename' => 'Jo',
            'surname' => 'Bloggs',
            'nickname' => 'Jojo',
            'dob' => '2001-02-03',
        ]);
        expectNotification('success', 'Changes saved');
    })->with(['staff', 'member', 'committee', 'super_admin']);

    it('should store the date of birth as 0000-00-00 when it is cleared', function () {
        $user = loginAs('member', ['dob' => '2001-02-03']);

        updateProfile(['update' => 'personal', 'name' => 'Jo Bloggs', 'nickname' => '', 'dob' => ''])->assertOk();

        expect(DB::table('users')->where('id', $user?->id)->value('dob'))->toBe('0000-00-00');
    })->group('known-bug', 'bug:zero-dates', 'mariadb');

    it('should validate the details', function (string $field, string $value, string $message) {
        loginAs('member');

        updateProfile(['update' => 'personal', 'name' => 'Jo Bloggs', $field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['name', '', 'Please enter your name'],
        ['name', 'Jo', 'Please enter your forename and surname'],
        ['name', 'Jo Anne Bloggs', 'Please enter your forename and surname'],
        ['name', 'José García', 'Please enter your forename and surname'],
        ['nickname', 'Jo2', 'Please just use letters'],
        ['dob', '03/02/2001', 'Please enter your DOB in the format YYYY-MM-DD'],
    ]);

    it('should accept names with hyphens and apostrophes', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'personal', 'name' => "Mary-Jane O'Brien"])->assertOk();

        assertDatabaseHas('users', ['id' => $user?->id, 'forename' => 'Mary-Jane', 'surname' => "O'Brien"]);
    });
});

describe('contact details', function () {
    it('should change them', function () {
        $user = loginAs('member');

        updateProfile([
            'update' => 'contact',
            'email' => 'new@example.com',
            'phone' => '07700900123',
            'address' => "1 New Road\nBath",
        ])->assertOk();

        assertDatabaseHas('users', [
            'id' => $user?->id,
            'email' => 'new@example.com',
            'phone' => '07700900123',
            'address' => "1 New Road\nBath",
        ]);
    });

    it('should let them keep their own email', function () {
        $user = loginAs('member', ['email' => 'mine@example.com']);

        updateProfile(['update' => 'contact', 'email' => 'mine@example.com'])->assertOk();
    });

    it('should validate the details', function (string $field, string $value, string $message) {
        createUser('member', ['email' => 'taken@example.com']);
        loginAs('member');

        updateProfile(['update' => 'contact', 'email' => 'me@example.com', $field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['email', '', 'Please enter your email address'],
        ['email', 'not-an-email', 'Please enter a valid email address'],
        ['email', 'taken@example.com', 'That email address is already in use by another user'],
        ['phone', '12345', 'Please enter a valid phone number'],
        ['phone', '+1 555 123 4567', 'Please enter a valid phone number'],
    ]);

    it('should accept UK phone numbers with spaces, but cut them off at 13 characters', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'contact', 'email' => 'me@example.com', 'phone' => '+44 7700 900 123'])->assertOk();

        assertDatabaseHas('users', ['id' => $user?->id, 'phone' => '+44 7700 900 ']);
    })->group('known-bug', 'bug:phone-truncated', 'mariadb');
});

describe('the profile picture', function () {
    it('should only let members upload one', function (string $group, int $status) {
        $user = loginAs($group);

        updateProfile(['update' => 'avatar', 'avatar' => UploadedFile::fake()->image('me.png')])->assertStatus($status);

        expect(File::exists(avatarPath($user ?? createUser())))->toBe($status === 200);
    })->with([['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should crop it to a 500x500 JPEG named after their username', function () {
        $user = loginAs('member');
        assert($user instanceof User);

        updateProfile(['update' => 'avatar', 'avatar' => UploadedFile::fake()->image('me.png', 800, 600)])
            ->assertOk()
            ->assertExactJson(['response' => 'Profile picture changed']);

        $image = getimagesize(avatarPath($user));
        expect($image)->not->toBeFalse();
        expect([$image[0] ?? null, $image[1] ?? null, $image['mime'] ?? null])->toBe([500, 500, 'image/jpeg']);
        expectNotification('success', 'Profile picture changed');
    });

    it('should require a file', function () {
        loginAs('member');

        updateProfile(['update' => 'avatar'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar' => 'Please select an image to use']);
    });

    it('should accept any file and fail if it isn\'t an image', function () {
        loginAs('member');

        updateProfile([
            'update' => 'avatar',
            'avatar' => UploadedFile::fake()->createWithContent('notes.txt', 'not an image'),
        ])->assertServerError();
    })->group('known-bug', 'bug:avatar-not-checked');

    it('should remove it', function () {
        $user = loginAs('member');
        assert($user instanceof User);
        File::put(avatarPath($user), 'a jpeg');

        updateProfile(['remove' => 'avatar'])
            ->assertOk()
            ->assertExactJson(['response' => 'Profile picture removed']);

        expect(File::exists(avatarPath($user)))->toBeFalse();
        expectNotification('success', 'Profile picture removed');
    });

    it('should do nothing if there is no picture to remove', function () {
        loginAs('member');

        updateProfile(['remove' => 'avatar'])
            ->assertOk()
            ->assertContent('');
    });

    it('should only let members remove one', function () {
        $user = loginAs('staff');
        assert($user instanceof User);
        File::put(avatarPath($user), 'a jpeg');

        updateProfile(['remove' => 'avatar'])->assertForbidden();

        expect(File::exists(avatarPath($user)))->toBeTrue();
    });
});

describe('privacy settings', function () {
    it('should save which details are shown', function () {
        $user = loginAs('member', ['show_email' => true]);

        updateProfile(['update' => 'privacy', 'show_phone' => '1', 'show_age' => '1'])
            ->assertOk()
            ->assertExactJson(['response' => 'Privacy settings updated']);

        assertDatabaseHas('users', [
            'id' => $user?->id,
            'show_email' => false,
            'show_phone' => true,
            'show_address' => false,
            'show_age' => true,
        ]);
    });

    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        updateProfile(['update' => 'privacy'])->assertStatus($status);
    })->with([['staff', 403], ['member', 200], ['super_admin', 403]]);
});

describe('other settings', function () {
    it('should save the tool colours', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'other', 'tool_colours' => 'Red & blue'])->assertOk();

        assertDatabaseHas('users', ['id' => $user?->id, 'tool_colours' => 'Red &amp; blue']);
    });

    it('should create an export token for the iCal feed', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'other', 'tool_colours' => '', 'event_export' => '1'])->assertOk();

        expect(strlen((string) $user?->fresh()?->export_token))->toBe(100);
    });

    it('should keep the export token if it is still wanted', function () {
        $user = loginAs('member', ['export_token' => 'existing-token']);

        updateProfile(['update' => 'other', 'tool_colours' => '', 'event_export' => '1'])->assertOk();

        assertDatabaseHas('users', ['id' => $user?->id, 'export_token' => 'existing-token']);
    });

    it('should remove the export token', function () {
        $user = loginAs('member', ['export_token' => 'existing-token']);

        updateProfile(['update' => 'other', 'tool_colours' => ''])->assertOk();

        assertDatabaseHas('users', ['id' => $user?->id, 'export_token' => null]);
    });

    it('should log the export token in plain text', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'other', 'tool_colours' => '', 'event_export' => '1'])->assertOk();

        $token = $user?->fresh()?->export_token;
        $log = Log::where('action', 'user.edit')->latest('id')->first();
        expect($log?->payload)->toMatchArray(['export_token' => $token]);
    })->group('known-bug', 'bug:tokens-in-audit-log');

    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        updateProfile(['update' => 'other', 'tool_colours' => ''])->assertStatus($status);
    })->with([['staff', 403], ['member', 200], ['super_admin', 403]]);
});

describe('diary preferences', function () {
    it('should save the types and crewing filter', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'diary-preferences', 'event_types' => ['event', 'social'], 'crewing' => 'true'])
            ->assertOk()
            ->assertExactJson(['response' => 'Saved.']);

        expect($user?->fresh()?->diary_preferences)->toBe(['event_types' => ['event', 'social'], 'crewing' => 'true']);
    });

    it('should reject invalid preferences', function (array $data) {
        loginAs('member');

        updateProfile(['update' => 'diary-preferences'] + $data)
            ->assertUnprocessable()
            ->assertJson(['error' => 'Your preferences could not be saved.']);
    })->with([
        'no types' => [['crewing' => '*']],
        'types not a list' => [['event_types' => 'event', 'crewing' => '*']],
        'unknown crewing filter' => [['event_types' => ['event'], 'crewing' => 'maybe']],
        'not crewing' => [['event_types' => ['event'], 'crewing' => 'false']],
    ]);

    it('should accept unknown event types', function () {
        $user = loginAs('member');

        updateProfile(['update' => 'diary-preferences', 'event_types' => ['parties'], 'crewing' => '*'])->assertOk();

        expect($user?->fresh()?->diary_preferences['event_types'] ?? null)->toBe(['parties']);
    });

    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        updateProfile([
            'update' => 'diary-preferences',
            'event_types' => ['event'],
            'crewing' => '*',
        ])->assertStatus($status);
    })->with([['staff', 403], ['member', 200], ['super_admin', 403]]);
});

it('should let a member edit only their own profile', function () {
    $other = createUser('member', ['forename' => 'Other']);
    actingAs(createUser('committee'));

    updateProfile(['update' => 'personal', 'name' => 'Changed Name', 'id' => $other->id])->assertOk();

    assertDatabaseHas('users', ['id' => $other->id, 'forename' => 'Other']);
});
