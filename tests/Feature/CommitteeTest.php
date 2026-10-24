<?php

namespace Tests\Feature;

use App\Models\Committee\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;

function createCommitteeRole(): Role
{
    return Role::create([
        'name' => 'Production Manager',
        'description' => 'Looks after the kit',
        'email' => 'pm@example.com',
        'order' => 1,
    ]);
}

it('should show the committee to guests', function () {
    createCommitteeRole();

    get(route('committee.view'))->assertOk()->assertSeeText('Production Manager');
});

describe('deleting a committee role', function () {
    it('should not allow guests', function () {
        $role = createCommitteeRole();

        postAjax(route('committee.delete'), ['id' => $role->id])->assertUnauthorized();

        assertModelExists($role);
    });

    it('should not allow members', function () {
        $role = createCommitteeRole();

        actingAs(createUser('member'));
        postAjax(route('committee.delete'), ['id' => $role->id])->assertForbidden();

        assertModelExists($role);
    });

    it('should allow admins', function () {
        $role = createCommitteeRole();

        actingAs(createUser('committee'));
        postAjax(route('committee.delete'), ['id' => $role->id])->assertOk();

        assertModelMissing($role);
    });
});
