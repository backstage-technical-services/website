<?php

namespace Tests\Feature\Users\Committee;

use App\Models\Committee\Role;
use App\Models\Users\Group;
use App\Models\Users\User;
use Database\Factories\Committee\RoleFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The public committee page, and the modals admins use to add, edit and delete roles.
 */

function createCommitteeRole(?User $holder = null, string $name = 'Production Manager'): Role
{
    $factory = RoleFactory::new();

    return ($holder ? $factory->heldBy($holder) : $factory)->createOne([
        'name' => $name,
        'description' => 'Looks after the kit',
        'email' => 'pm@example.com',
    ]);
}

function groupName(User $user): ?string
{
    return Group::find($user->fresh()?->user_group_id)?->name;
}

/**
 * @return array<int, string>
 */
function roleOrder(): array
{
    return Role::orderBy('order')->pluck('name', 'order')->all();
}

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function roleForm(array $changes = []): array
{
    return array_merge(
        [
            'name' => 'Secretary',
            'email' => 'sec@example.com',
            'description' => '[name] keeps the minutes',
            'user_id' => '',
            'order' => '1',
        ],
        $changes,
    );
}

describe('the committee page', function () {
    it('should show the committee to everyone', function (string $group) {
        createCommitteeRole();
        loginAs($group);

        get(route('committee.view'))->assertOk()->assertSeeText('Production Manager');
    })->with(['guest', 'staff', 'member', 'super_admin']);

    it('should list the roles in order with their holders and mailboxes', function () {
        $holder = createUser('committee', ['forename' => 'Chris', 'surname' => 'Chair']);
        RoleFactory::new()
            ->heldBy($holder)
            ->createOne(['name' => 'Chair', 'email' => 'chair@bts-crew.com', 'order' => 1]);
        RoleFactory::new()->createOne(['name' => 'Treasurer', 'email' => 'treas@bts-crew.com', 'order' => 2]);

        get(route('committee.view'))
            ->assertOk()
            ->assertSeeTextInOrder([
                'chair@bts-crew.com',
                'Chair',
                'Chris Chair',
                'treas@bts-crew.com',
                'Treasurer',
                'Unelected',
            ])
            ->assertSee('src="/images/profiles/blank.jpg"', false);
    });

    it('should put the holder\'s first name in the description', function () {
        $holder = createUser('committee', ['forename' => 'Chris']);
        RoleFactory::new()
            ->heldBy($holder)
            ->createOne(['name' => 'Chair', 'description' => '**[name]** runs meetings']);
        RoleFactory::new()->createOne(['name' => 'Treasurer', 'description' => '[name] counts the money']);

        get(route('committee.view'))
            ->assertOk()
            ->assertSee('<strong>Chris</strong> runs meetings', false)
            ->assertSee('The treasurer counts the money', false);
    });

    it('should only show the admin tools to admins', function (string $group, bool $shown) {
        createCommitteeRole();
        loginAs($group);

        $response = get(route('committee.view'))->assertOk();
        $shown
            ? $response->assertSee('data-save-action="' . route('committee.edit') . '"', false)
            : $response->assertDontSee('data-save-action="' . route('committee.edit') . '"', false);
    })->with([['guest', false], ['member', false], ['committee', true], ['super_admin', true]]);
});

describe('adding a committee role', function () {
    it('should only let admins add roles', function (string $group, int $status) {
        loginAs($group);

        postAjax(route('committee.add'), roleForm())->assertStatus($status);

        assertDatabaseCount('committee_roles', $status === 200 ? 1 : 0);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should only accept AJAX requests', function () {
        loginAs('committee');

        post(route('committee.add'), roleForm())->assertNotFound();
    });

    it('should add the role', function () {
        $holder = createUser('member');
        loginAs('committee');

        postAjax(route('committee.add'), roleForm(['user_id' => (string) $holder->id, 'name' => 'Sec & Social']))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        assertDatabaseHas('committee_roles', [
            'name' => 'Sec &amp; Social',
            'email' => 'sec@example.com',
            'description' => '[name] keeps the minutes',
            'user_id' => $holder->id,
            'order' => 1,
        ]);
        expectNotification('success', 'Committee role added');
    });

    it('should put the holder in the committee group', function (string $group) {
        $holder = createUser($group);
        loginAs('committee');

        postAjax(route('committee.add'), roleForm(['user_id' => (string) $holder->id]))->assertOk();

        expect(groupName($holder))->toBe('committee');
    })->with(['member', 'associate', 'staff', 'super_admin']);

    it('should put the role in the chosen place and move the others down', function () {
        RoleFactory::new()->createOne(['name' => 'Chair', 'order' => 1]);
        RoleFactory::new()->createOne(['name' => 'Treasurer', 'order' => 2]);
        loginAs('committee');

        postAjax(route('committee.add'), roleForm(['name' => 'Secretary', 'order' => '2']))->assertOk();

        expect(roleOrder())->toBe([1 => 'Chair', 2 => 'Secretary', 3 => 'Treasurer']);
    });

    it('should put the role at the end if the place is out of range', function (string $order) {
        RoleFactory::new()->createOne(['name' => 'Chair', 'order' => 1]);
        loginAs('committee');

        postAjax(route('committee.add'), roleForm(['name' => 'Secretary', 'order' => $order]))->assertOk();

        expect(roleOrder())->toBe([1 => 'Chair', 2 => 'Secretary']);
    })->with(['0', '9']);

    it('should validate the role', function (string $field, string $value, string $message) {
        loginAs('committee');

        postAjax(route('committee.add'), roleForm([$field => $value]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['name', '', "Please enter the role's name"],
        ['email', '', "Please enter the role's email address"],
        ['email', 'not-an-email', 'Please enter a valid email address'],
        ['description', '', 'Please enter a role description'],
        ['user_id', '999', 'Please choose a valid user'],
        ['order', '', 'Please select where this role should appear'],
    ]);
});

describe('editing a committee role', function () {
    it('should only let admins edit roles', function (string $group, int $status) {
        $role = createCommitteeRole();
        loginAs($group);

        postAjax(route('committee.edit'), roleForm(['id' => $role->id, 'name' => 'Renamed']))->assertStatus($status);

        assertDatabaseHas('committee_roles', [
            'id' => $role->id,
            'name' => $status === 200 ? 'Renamed' : 'Production Manager',
        ]);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should update the role', function () {
        $role = createCommitteeRole();
        loginAs('committee');

        postAjax(route('committee.edit'), roleForm(['id' => $role->id, 'name' => 'Equipment Officer']))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        assertDatabaseHas('committee_roles', [
            'id' => $role->id,
            'name' => 'Equipment Officer',
            'email' => 'sec@example.com',
        ]);
        expectNotification('success', "Role 'Equipment Officer' updated");
    });

    it('should say if the role can\'t be found', function () {
        loginAs('committee');

        postAjax(route('committee.edit'), roleForm(['id' => 999]))
            ->assertUnprocessable()
            ->assertJson(['error' => 'Could not find the committee role.']);
    });

    it('should move the role', function (int $from, string $to, array $order) {
        foreach (['A', 'B', 'C', 'D'] as $i => $name) {
            RoleFactory::new()->createOne(['name' => $name, 'order' => $i + 1]);
        }
        $role = Role::where('order', $from)->firstOrFail();
        loginAs('committee');

        postAjax(
            route('committee.edit'),
            roleForm(['id' => $role->id, 'name' => $role->name, 'order' => $to]),
        )->assertOk();

        expect(roleOrder())->toBe($order);
    })->with([
        'later' => [1, '4', [1 => 'B', 2 => 'C', 3 => 'A', 4 => 'D']],
        'earlier' => [4, '2', [1 => 'A', 2 => 'D', 3 => 'B', 4 => 'C']],
        'to the end' => [2, '9', [1 => 'A', 2 => 'C', 3 => 'D', 4 => 'B']],
    ]);

    it('should move the old holder back to being a member, and make the new one committee', function () {
        $old = createUser('committee');
        $new = createUser('member');
        $role = createCommitteeRole($old);
        loginAs('committee');

        postAjax(
            route('committee.edit'),
            roleForm(['id' => $role->id, 'user_id' => (string) $new->id, 'order' => '1']),
        )->assertOk();

        assertDatabaseHas('committee_roles', ['id' => $role->id, 'user_id' => $new->id]);
        expect(groupName($old))->toBe('member');
        expect(groupName($new))->toBe('committee');
    });

    it('should keep the old holder on the committee if they hold another role', function () {
        $old = createUser('committee');
        $role = createCommitteeRole($old);
        createCommitteeRole($old, 'Secretary');
        loginAs('committee');

        postAjax(
            route('committee.edit'),
            roleForm(['id' => $role->id, 'name' => $role->name, 'order' => '1']),
        )->assertOk();

        assertDatabaseHas('committee_roles', ['id' => $role->id, 'user_id' => null]);
        expect(groupName($old))->toBe('committee');
    });

    it('should leave a non-committee holder in their group when they are removed', function () {
        $old = createUser('super_admin');
        $role = createCommitteeRole($old);
        loginAs('committee');

        postAjax(route('committee.edit'), roleForm(['id' => $role->id, 'order' => '1']))->assertOk();

        expect(groupName($old))->toBe('super_admin');
    });

    it('should not let admins remove themselves from a role', function () {
        $admin = createUser('committee');
        $role = createCommitteeRole($admin);
        actingAs($admin);

        postAjax(route('committee.edit'), roleForm(['id' => $role->id, 'order' => '1']))->assertOk();

        assertDatabaseHas('committee_roles', ['id' => $role->id, 'user_id' => $admin->id]);
        expect(groupName($admin))->toBe('committee');
        expectNotification('warning', "You can't remove yourself from the committee");
    });
});

describe('deleting a committee role', function () {
    it('should not allow guests', function () {
        $role = createCommitteeRole();

        postAjax(route('committee.delete'), ['id' => $role->id])->assertUnauthorized();

        assertModelExists($role);
    });

    it('should only let admins delete roles', function (string $group, int $status) {
        $role = createCommitteeRole();
        loginAs($group);

        postAjax(route('committee.delete'), ['id' => $role->id])->assertStatus($status);

        $status === 200 ? assertModelMissing($role) : assertModelExists($role);
    })->with([['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should move the later roles up', function () {
        foreach (['A', 'B', 'C'] as $i => $name) {
            RoleFactory::new()->createOne(['name' => $name, 'order' => $i + 1]);
        }
        loginAs('committee');

        postAjax(route('committee.delete'), ['id' => Role::where('name', 'A')->value('id')])->assertOk();

        expect(roleOrder())->toBe([1 => 'B', 2 => 'C']);
        expectNotification('success', 'Committee role deleted');
    });

    it('should leave the holder in the committee group', function () {
        $holder = createUser('committee');
        $role = createCommitteeRole($holder);
        loginAs('committee');

        postAjax(route('committee.delete'), ['id' => $role->id])->assertOk();

        expect(groupName($holder))->toBe('committee');
    })->group('known-bug', 'bug:committee-role-drift');

    it('should say if the role can\'t be found', function () {
        loginAs('committee');

        postAjax(route('committee.delete'), ['id' => 999])
            ->assertUnprocessable()
            ->assertJson(['error' => 'Could not find the committee role.']);
    });

    it('should only accept AJAX requests', function () {
        $role = createCommitteeRole();
        loginAs('committee');

        post(route('committee.delete'), ['id' => $role->id])->assertNotFound();

        assertModelExists($role);
    });
});
