<?php

namespace Tests;

use App\Models\Users\Group;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;
    
    protected function assertShowsNotFound(TestResponse $response)
    {
        $response->assertNotFound()
            ->assertSeeText("404")
            ->assertSeeText("We couldn't find what you were looking for", false);
    }
    
    protected function createMember($persist = true): User
    {
        return $this->createUser(
            groupTitle: 'Member',
            groupName: 'member',
            persist: $persist
        );
    }
    
    protected function createCommitteeMember($persist = true): User
    {
        return $this->createUser(
            groupTitle: 'Committee Member',
            groupName: 'committee',
            persist: $persist
        );
    }
    
    protected function createAdmin($persist = true): User
    {
        return $this->createUser(
            groupTitle: 'Super Admin',
            groupName: 'super_admin',
            persist: $persist
        );
    }
    
    private function createUser(string $groupTitle, string $groupName, bool $persist): User
    {
        $user = User::factory()
            ->for(
                Group::factory()
                    ->state([
                        'title' => $groupTitle,
                        'name'  => $groupName
                    ])
            );
        
        return match ($persist) {
            true => $user->create(),
            false => $user->make()
        };
    }
}
