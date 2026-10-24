<?php

use App\Models\Users\Group;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\post;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->use(Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create an active user in the given group (eg, member, committee, super_admin). This doesn't use User::create() as
 * that also creates the user in Keycloak and sends them an email.
 *
 * @param array<string, mixed> $attributes
 */
function createUser(string $group = 'member', array $attributes = []): User
{
    $groupId =
        Group::where('name', $group)->value('id') ??
        DB::table('user_groups')->insertGetId(['title' => User::$AccountTypes[$group], 'name' => $group]);
    $username = $attributes['username'] ?? Str::lower(Str::random(10));

    $user = new User();
    $user->forceFill(
        $attributes + [
            'username' => $username,
            'email' => "{$username}@example.com",
            'password' => '',
            'forename' => 'Test',
            'surname' => 'User',
            'diary_preferences' => [],
            'status' => true,
            'user_group_id' => $groupId,
        ],
    );
    $user->save();

    return $user;
}

/**
 * Make a POST request in the same way as the site's JavaScript (a jQuery JSON request), as some endpoints only accept
 * AJAX requests.
 *
 * @param array<string, mixed> $data
 *
 * @return TestResponse<Response>
 */
function postAjax(string $uri, array $data = []): TestResponse
{
    return post($uri, $data, ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
}
