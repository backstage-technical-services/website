<?php

namespace Tests\Feature\Users\UserAdmin;

use App\Models\Users\Group;
use App\Models\Users\User;
use App\Notifications\Users\UserAccountCreated;
use Database\Factories\Committee\RoleFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The user admin pages, at /users.
 */

function groupOf(User $user): ?string
{
    return Group::find($user->fresh()?->user_group_id)?->name;
}

/**
 * The edit form, as the browser sends it, with any changes.
 *
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function editForm(User $user, array $changes = []): array
{
    return array_filter(
        array_merge(
            [
                'action' => 'save',
                'name' => $user->name,
                'username' => $user->username,
                'nickname' => (string) $user->nickname,
                'email' => $user->email,
                'phone' => (string) $user->phone,
                'dob' => '',
                'address' => (string) $user->address,
                'tool_colours' => (string) $user->tool_colours,
                'type' => 'member',
                'show_email' => $user->show_email ? '1' : null,
            ],
            $changes,
        ),
        fn($value) => $value !== null,
    );
}

describe('the list of users', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('user.index')), $access);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list every user, 20 a page, by surname', function () {
        foreach (range(1, 21) as $i) {
            createUser('member', ['surname' => sprintf('Surname%02d', $i)]);
        }
        loginAs('committee', ['surname' => 'Zzz']);

        get(route('user.index'))->assertOk()->assertSeeText('Surname01')->assertDontSeeText('Surname21');
        get(route('user.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Surname21');
    });

    it('should show each user\'s account type', function (string $group, string $type) {
        createUser($group, ['forename' => 'The', 'surname' => 'User']);
        loginAs('committee', ['surname' => 'Admin']);

        get(route('user.index'))
            ->assertOk()
            ->assertSeeInOrder(['The User', $type]);
    })->with([
        ['member', 'Member'],
        ['associate', 'Associate'],
        ['committee', 'Committee'],
        ['staff', 'Staff'],
        ['super_admin', 'Admin'],
        ['archived', 'Archived'],
    ]);

    it('should search the users', function () {
        createUser('member', ['forename' => 'Findable', 'surname' => 'Person']);
        createUser('member', ['forename' => 'Other', 'surname' => 'Person']);
        loginAs('committee');

        get(route('user.index', ['search' => 'Findable']))
            ->assertOk()
            ->assertSeeText('Findable Person')
            ->assertDontSeeText('Other Person');
    });

    it('should filter the users, without paginating', function (string $filter, array $shown, array $hidden) {
        createUser('member', ['forename' => 'A', 'surname' => 'Member']);
        createUser('committee', ['forename' => 'A', 'surname' => 'Committee']);
        createUser('staff', ['forename' => 'A', 'surname' => 'Staff']);
        createUser('archived', ['forename' => 'An', 'surname' => 'Archived']);
        loginAs('super_admin');

        get(route('user.index', ['filter' => $filter]))
            ->assertOk()
            ->assertSeeText($shown)
            ->assertDontSeeText($hidden);
    })->with([
        ['archived', ['An Archived'], ['A Member', 'A Staff']],
        ['active', ['A Member', 'A Staff'], ['An Archived']],
        ['committee', ['A Committee'], ['A Member', 'A Staff']],
        ['staff', ['A Staff'], ['A Member', 'A Committee']],
        ['all', ['A Member', 'A Staff', 'An Archived'], []],
    ]);

    it('should not show the filter options, as the search tools are bound under the wrong name', function () {
        loginAs('committee');

        get(route('user.index'))->assertOk()->assertDontSee('<div class="dropdown filter">', false);
    })->group('known-bug', 'bug:search-tools-filter');

    it('should not offer to archive yourself', function () {
        $admin = loginAs('committee');

        get(route('user.index'))
            ->assertOk()
            ->assertDontSee('name="archive-user" title="Archive" value="' . $admin?->id . '"', false);
    });
});

describe('creating users', function () {
    it('should only let admins see the form', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('user.create')), $access);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should only let admins create users', function (string $group, int $status) {
        Notification::fake();
        loginAs($group);

        post(route('user.store'), [
            'mode' => 'single',
            'name' => 'New Person',
            'username' => 'np123',
            'type' => 'member',
        ])->assertStatus($status);

        $status === 302
            ? assertDatabaseHas('users', ['username' => 'np123'])
            : assertDatabaseMissing('users', ['username' => 'np123']);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should go back to the form for an unknown mode', function () {
        loginAs('committee');

        post(route('user.store'), ['mode' => 'other'])->assertRedirect(route('user.create'));
    });

    describe('one user', function () {
        it('should create the user, and their Keycloak account, and email them a temporary password', function () {
            Notification::fake();
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'single',
                'name' => 'New Person',
                'username' => 'np123',
                'type' => 'associate',
            ])->assertRedirect(route('user.index'));

            $user = User::where('username', 'np123')->first();
            assert($user instanceof User);
            assertDatabaseHas('users', [
                'id' => $user->id,
                'forename' => 'New',
                'surname' => 'Person',
                'email' => 'np123@bath.ac.uk',
                'status' => true,
            ]);
            expect(groupOf($user))->toBe('associate');
            expect($user->diary_preferences)->toBe([
                'event_types' => ['event', 'training', 'social', 'meeting', 'hidden'],
                'crewing' => '*',
            ]);

            $keycloakUsers = fakeKeycloak()->users();
            expect($keycloakUsers)->toHaveCount(1);
            $keycloakId = (string) array_key_first($keycloakUsers);
            $keycloakUser = $keycloakUsers[$keycloakId];
            expect($keycloakUser)->toMatchArray([
                'username' => 'np123',
                'firstName' => 'New',
                'lastName' => 'Person',
                'email' => 'np123@bath.ac.uk',
            ]);
            expect(strlen((string) $keycloakUser['password']))->toBe(15);
            expect(fakeKeycloak()->hasAccessRole($keycloakId))->toBeTrue();
            expect($user->keycloak_user_id)->toBe($keycloakId);

            Notification::assertSentTo($user, function (UserAccountCreated $notification) use ($user, $keycloakUser) {
                $mail = $notification->toMail($user);

                expect($mail->subject)->toBe('[Backstage Website] Your new Backstage account');
                expect($mail->replyTo)->toBe([['sec@bts-crew.com', null]]);
                expect($mail->actionUrl)->toBe(route('auth.login'));
                expect($mail->introLines[1])->toBe(
                    "Your temporary password is `{$keycloakUser['password']}`. You will be prompted to update this to something more memorable the first time you log in.",
                );

                return true;
            });
            expectNotification('success', 'User created');
        });

        it('should link an existing Keycloak account without a password', function (string $match) {
            Notification::fake();
            $keycloakId = fakeKeycloak()->addUser(
                $match === 'username' ? 'np123' : 'someone-else',
                $match === 'email' ? 'np123@bath.ac.uk' : 'other@example.com',
            );
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'single',
                'name' => 'New Person',
                'username' => 'np123',
                'type' => 'member',
            ])->assertRedirect(route('user.index'));

            $user = User::where('username', 'np123')->first();
            assert($user instanceof User);
            expect($user->keycloak_user_id)->toBe($keycloakId);
            expect(fakeKeycloak()->users())->toHaveCount(1);
            expect(fakeKeycloak()->hasAccessRole($keycloakId))->toBeTrue();
            Notification::assertSentTo(
                $user,
                fn(UserAccountCreated $notification) => $notification->toMail($user)->introLines[1] ===
                    'You can log in using your existing Backstage identity.',
            );
        })->with(['username', 'email']);

        it('should remove the user again if Keycloak fails, and fail', function () {
            Notification::fake();
            fakeKeycloak()->failWith(new RuntimeException('Keycloak is down'));
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'single',
                'name' => 'New Person',
                'username' => 'np123',
                'type' => 'member',
            ])->assertServerError();

            assertDatabaseMissing('users', ['username' => 'np123']);
            Notification::assertNothingSent();
        });

        it('should validate the user', function (string $field, string $value, string $message) {
            createUser('member', ['username' => 'taken1']);
            loginAs('committee');

            from(route('user.create'))
                ->post(
                    route('user.store'),
                    array_merge(
                        ['mode' => 'single', 'name' => 'New Person', 'username' => 'np123', 'type' => 'member'],
                        [$field => $value],
                    ),
                )
                ->assertRedirect(route('user.create'))
                ->assertSessionHasErrors([$field => $message])
                ->assertSessionHasInput('mode', 'single');

            assertDatabaseCount('users', 2);
        })->with([
            ['name', '', "Please enter the new user's name"],
            ['name', 'Single', 'Please enter their forename and surname'],
            ['username', '', "Please enter the new user's BUCS username"],
            ['username', 'np 123', 'Please enter just their username'],
            ['username', 'taken1', 'A user with that username already exists'],
            ['type', '', 'Please select an account type'],
            ['type', 'admin', 'Please select one of the provided account types'],
        ]);

        it('should let admins create super admins', function () {
            Notification::fake();
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'single',
                'name' => 'Web Master',
                'username' => 'wm123',
                'type' => 'super_admin',
            ])->assertRedirect(route('user.index'));

            $user = User::where('username', 'wm123')->first();
            assert($user instanceof User);
            expect(groupOf($user))->toBe('super_admin');
        });
    });

    describe('several users', function () {
        it('should create each user and show a summary', function () {
            Notification::fake();
            createUser('member', ['username' => 'taken1']);
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'bulk',
                'type' => 'member',
                'users' => "Ann One,ao111\r\nBob Two,taken1\r\nCat Three,ct333",
            ])->assertRedirect(route('user.create.summary'));

            assertDatabaseHas('users', ['username' => 'ao111', 'forename' => 'Ann']);
            assertDatabaseHas('users', ['username' => 'ct333', 'forename' => 'Cat']);
            Notification::assertCount(2);

            get(route('user.create.summary'))
                ->assertOk()
                ->assertSeeInOrder(['ao111', 'User created successfully'])
                ->assertSeeInOrder(['taken1', 'A user with that username already exists'])
                ->assertSeeInOrder(['ct333', 'User created successfully']);
        });

        it('should only show the summary once', function () {
            Notification::fake();
            loginAs('committee');

            post(route('user.store'), ['mode' => 'bulk', 'type' => 'member', 'users' => 'Ann One,ao111']);

            get(route('user.create.summary'))->assertOk();
            get(route('user.create.summary'))->assertRedirect(route('user.create'));
        });

        it('should validate the list', function (string $users, string $message) {
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'bulk',
                'type' => 'member',
                'users' => $users,
            ])->assertSessionHasErrors(['users' => $message]);
        })->with([
            ['', 'Please enter the list of users to add'],
            [
                'Ann One ao111',
                'Please enter each user in the format specified in the <a href="#" data-toggle="modal" data-target="#modal" data-modal-template="help">help</a>.',
            ],
        ]);

        it('should only check that one line is in the right format, and fail on a blank line', function () {
            Notification::fake();
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'bulk',
                'type' => 'member',
                'users' => "Ann One,ao111\r\n\r\nBob Two,bt222",
            ])->assertServerError();

            assertDatabaseHas('users', ['username' => 'ao111']);
            assertDatabaseMissing('users', ['username' => 'bt222']);
            Notification::assertCount(1);
        })->group('known-bug', 'bug:bulk-create-crash');

        it('should stop part-way through if Keycloak fails', function () {
            Notification::fake();
            fakeKeycloak()->failWith(new RuntimeException('Keycloak is down'), 'bt222');
            loginAs('committee');

            post(route('user.store'), [
                'mode' => 'bulk',
                'type' => 'member',
                'users' => "Ann One,ao111\r\nBob Two,bt222\r\nCat Three,ct333",
            ])->assertServerError();

            assertDatabaseHas('users', ['username' => 'ao111']);
            assertDatabaseMissing('users', ['username' => 'bt222']);
            assertDatabaseMissing('users', ['username' => 'ct333']);
            Notification::assertCount(1);
        })->group('known-bug', 'bug:bulk-create-crash');
    });
});

describe('viewing a user', function () {
    it('should redirect to their profile', function () {
        $user = createUser('member');

        get(route('user.view', ['username' => $user->username]))->assertRedirect(
            route('member.view', ['username' => $user->username]),
        );
    });
});

describe('editing a user', function () {
    it('should only let admins see the form', function (string $group, string|int $access) {
        $user = createUser('member');
        loginAs($group);

        expectAccess(get(route('user.edit', ['username' => $user->username])), $access);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should give a 404 for usernames with a dot', function () {
        createUser('member', ['username' => 'first.last']);
        loginAs('committee');

        get('/users/first.last/edit')->assertNotFound();
    })->group('known-bug', 'bug:dotted-usernames');

    it('should not select the user\'s account type in the form', function () {
        $user = createUser('committee');
        loginAs('committee');

        get(route('user.edit', ['username' => $user->username]))
            ->assertOk()
            ->assertSee('<option value="committee">Committee</option>', false)
            ->assertDontSee('selected="selected"', false);
    })->group('known-bug', 'bug:edit-user-demotes');

    it('should only let admins change a user', function (string $group, int $status) {
        $user = createUser('member', ['forename' => 'Old']);
        loginAs($group);

        post(
            route('user.update', ['username' => $user->username]),
            editForm($user, ['name' => 'New Name']),
        )->assertStatus($status);

        assertDatabaseHas('users', ['id' => $user->id, 'forename' => $status === 302 ? 'New' : 'Old']);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should change the user\'s details', function () {
        $user = createUser('member');
        loginAs('committee');

        post(
            route('user.update', ['username' => $user->username]),
            editForm($user, [
                'name' => 'Changed Name',
                'username' => 'cn123',
                'email' => 'changed@example.com',
                'phone' => '07700900123',
                'dob' => '2000-01-02',
                'type' => 'associate',
                'show_email' => null,
                'show_phone' => '1',
            ]),
        )->assertRedirect(route('user.index'));

        assertDatabaseHas('users', [
            'id' => $user->id,
            'forename' => 'Changed',
            'surname' => 'Name',
            'username' => 'cn123',
            'email' => 'changed@example.com',
            'phone' => '07700900123',
            'dob' => '2000-01-02',
            'show_email' => false,
            'show_phone' => true,
        ]);
        expect(groupOf($user))->toBe('associate');
        expectNotification('success', 'User updated');
    });

    it('should demote the user to a member when the form is saved as it is', function () {
        $user = createUser('committee');
        loginAs('committee');

        post(route('user.update', ['username' => $user->username]), editForm($user))->assertRedirect(
            route('user.index'),
        );

        expect(groupOf($user))->toBe('member');
    })->group('known-bug', 'bug:edit-user-demotes');

    it('should ignore changes to the nickname, address and tool colours', function () {
        $user = createUser('member', ['nickname' => 'Old', 'address' => 'Old Road', 'tool_colours' => 'Red']);
        loginAs('committee');

        post(
            route('user.update', ['username' => $user->username]),
            editForm($user, ['nickname' => 'New', 'address' => 'New Road', 'tool_colours' => 'Blue']),
        )->assertRedirect(route('user.index'));

        assertDatabaseHas('users', [
            'id' => $user->id,
            'nickname' => 'Old',
            'address' => 'Old Road',
            'tool_colours' => 'Red',
        ]);
    })->group('known-bug', 'bug:edit-user-demotes');

    it('should not let admins change their own username or account type', function () {
        $admin = createUser('committee', ['username' => 'admin1']);
        actingAs($admin);

        post(
            route('user.update', ['username' => 'admin1']),
            editForm($admin, ['username' => 'renamed', 'type' => 'member']),
        )->assertRedirect(route('user.index'));

        assertDatabaseHas('users', ['id' => $admin->id, 'username' => 'admin1']);
        expect(groupOf($admin))->toBe('committee');
    });

    it('should validate the details', function (string $field, string $value, string $message) {
        createUser('member', ['email' => 'taken@example.com', 'username' => 'taken1']);
        $user = createUser('member');
        loginAs('committee');

        from(route('user.edit', ['username' => $user->username]))
            ->post(route('user.update', ['username' => $user->username]), editForm($user, [$field => $value]))
            ->assertRedirect(route('user.edit', ['username' => $user->username]))
            ->assertSessionHasErrors([$field => $message]);
    })->with([
        ['name', 'Single', 'Please enter your forename and surname'],
        ['username', 'taken1', 'A user with that username already exists'],
        ['email', 'taken@example.com', 'That email address is already in use by another user'],
        ['phone', '123', 'Please enter a valid phone number'],
        ['dob', '02/01/2000', 'Please enter your DOB in the format YYYY-MM-DD'],
        ['type', 'admin', 'The selected type is invalid.'],
    ]);

    it('should store the date of birth as 0000-00-00 when it is cleared', function () {
        $user = createUser('member', ['dob' => '2000-01-02']);
        loginAs('committee');

        post(route('user.update', ['username' => $user->username]), editForm($user, ['dob' => '']))->assertRedirect();

        expect(DB::table('users')->where('id', $user->id)->value('dob'))->toBe('0000-00-00');
    })->group('known-bug', 'bug:zero-dates', 'mariadb');

    it('should give a 404 for an unknown user', function () {
        loginAs('committee');

        post(route('user.update', ['username' => 'nobody']), ['action' => 'save'])->assertNotFound();
    });

    it('should go back to the form for an unknown action', function () {
        $user = createUser('member');
        loginAs('committee');

        post(route('user.update', ['username' => $user->username]), ['action' => 'other'])->assertRedirect(
            route('user.edit', ['username' => $user->username]),
        );
    });

    describe('archiving', function () {
        it('should archive and unarchive a user', function () {
            $user = createUser('member');
            loginAs('committee');

            post(route('user.update', ['username' => $user->username]), ['action' => 'archive'])->assertRedirect(
                route('user.edit', ['username' => $user->username]),
            );
            assertDatabaseHas('users', ['id' => $user->id, 'status' => false]);
            expectNotification('success', 'User archived');

            post(route('user.update', ['username' => $user->username]), ['action' => 'unarchive'])->assertRedirect();
            assertDatabaseHas('users', ['id' => $user->id, 'status' => true]);
            expectNotification('success', 'User unarchived');
        });

        it('should not let admins archive themselves', function () {
            $admin = loginAs('committee');
            assert($admin instanceof User);

            post(route('user.update', ['username' => $admin->username]), ['action' => 'archive'])->assertRedirect();

            assertDatabaseHas('users', ['id' => $admin->id, 'status' => true]);
            expectNotification('warning', "You can't modify the status of your own account.");
        });

        it('should keep the user\'s committee roles and group', function () {
            $user = createUser('committee');
            RoleFactory::new()->heldBy($user)->createOne();
            loginAs('committee');

            post(route('user.update', ['username' => $user->username]), ['action' => 'archive'])->assertRedirect();

            assertDatabaseHas('committee_roles', ['user_id' => $user->id]);
            expect(groupOf($user))->toBe('committee');
        })->group('known-bug', 'bug:committee-role-drift');
    });

    describe('the profile picture', function () {
        it('should change the user\'s picture', function () {
            $user = createUser('member');
            loginAs('committee');

            post(route('user.update', ['username' => $user->username]), [
                'action' => 'change-pic',
                'avatar' => UploadedFile::fake()->image('them.jpg', 600, 600),
            ])->assertRedirect(route('user.edit', ['username' => $user->username]));

            expect(File::exists(public_path("images/profiles/{$user->username}.jpg")))->toBeTrue();
            expectNotification('success', 'Profile picture changed');
        });

        it('should warn if no picture is chosen', function () {
            $user = createUser('member');
            loginAs('committee');

            post(route('user.update', ['username' => $user->username]), ['action' => 'change-pic'])->assertRedirect();

            expectNotification('warning', 'Please select an image to use');
        });

        it('should remove the user\'s picture', function () {
            $user = createUser('member');
            File::put(public_path("images/profiles/{$user->username}.jpg"), 'a jpeg');
            loginAs('committee');

            post(route('user.update', ['username' => $user->username]), ['action' => 'remove-pic'])->assertRedirect();

            expect(File::exists(public_path("images/profiles/{$user->username}.jpg")))->toBeFalse();
            expectNotification('success', 'Profile picture removed');
        });
    });
});

describe('archiving from the list', function () {
    it('should only let admins archive users', function (string $group, int $status) {
        $user = createUser('member');
        loginAs($group);

        post(route('user.update.bulk'), ['archive-user' => $user->id])->assertStatus($status);

        assertDatabaseHas('users', ['id' => $user->id, 'status' => $status !== 302]);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should archive a user', function () {
        $user = createUser('member');
        loginAs('committee');

        post(route('user.update.bulk'), ['archive-user' => $user->id])->assertRedirect(route('user.index'));

        assertDatabaseHas('users', ['id' => $user->id, 'status' => false]);
        expectNotification('success', 'User successfully archived');
    });

    it('should not let admins archive themselves', function () {
        $admin = loginAs('committee');

        post(route('user.update.bulk'), ['archive-user' => $admin?->id])->assertRedirect();

        assertDatabaseHas('users', ['id' => $admin?->id, 'status' => true]);
        expectNotification('warning', 'You cannot archive your own account');
    });

    it('should say if the user can\'t be found', function () {
        loginAs('committee');

        post(route('user.update.bulk'), ['archive-user' => 999])->assertRedirect();

        expectNotification('error', 'Could not find the user to archive');
    });
});

describe('bulk actions', function () {
    it('should apply the action to every user', function (string $action, string $message, array $expected) {
        $users = [createUser('member'), createUser('staff')];
        loginAs('committee');

        post(route('user.update.bulk'), [
            'bulk' => '1',
            'bulk-action' => $action,
            'users' => array_map(fn(User $user) => $user->id, $users),
        ])->assertRedirect(route('user.index'));

        foreach ($users as $user) {
            expect([groupOf($user), (bool) $user->fresh()?->status])->toBe($expected[$user->is($users[0]) ? 0 : 1]);
        }
        expectNotification('success', $message);
    })->with([
        ['archive', 'All of the selected users were succesfully archived', [['member', false], ['staff', false]]],
        [
            'committee',
            'All of the selected users were succesfully given committee access',
            [['committee', true], ['committee', true]],
        ],
        [
            'associate',
            'All of the selected users were succesfully made associates',
            [['associate', true], ['associate', true]],
        ],
    ]);

    it('should skip the admin\'s own account', function () {
        $admin = loginAs('committee');
        $user = createUser('member');

        post(route('user.update.bulk'), [
            'bulk' => '1',
            'bulk-action' => 'associate',
            'users' => [$admin?->id, $user->id],
        ])->assertRedirect();

        expectNotification('warning', '1 of the 2 users were successfully made associates');
        expectNotification('warning', 'You cannot change your own membership type');
    });

    it('should say when none of the users could be changed', function () {
        loginAs('committee');

        post(route('user.update.bulk'), [
            'bulk' => '1',
            'bulk-action' => 'archive',
            'users' => [999],
        ])->assertRedirect();

        expectNotification('error', 'None of the selected users could be archived');
    });

    it('should need an action and some users', function (array $data, string $message) {
        loginAs('committee');

        post(route('user.update.bulk'), ['bulk' => '1'] + $data)->assertRedirect();

        expectNotification('warning', $message);
    })->with([
        [['users' => [1]], 'Select a valid action'],
        [['bulk-action' => 'delete', 'users' => [1]], 'Select a valid action'],
        [['bulk-action' => 'archive'], 'Select some users'],
    ]);

    it('should not give the new committee members a committee role', function () {
        $user = createUser('member');
        loginAs('committee');

        post(route('user.update.bulk'), [
            'bulk' => '1',
            'bulk-action' => 'committee',
            'users' => [$user->id],
        ])->assertRedirect();

        assertDatabaseMissing('committee_roles', ['user_id' => $user->id]);
    })->group('known-bug', 'bug:committee-role-drift');
});
