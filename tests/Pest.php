<?php

use Alaouy\Youtube\Facades\Youtube;
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use App\Models\Users\User;
use App\Services\FinanceDb;
use Database\Factories\Users\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Package\Keycloak\KeycloakClient;
use Package\Notifications\Notification;
use Pest\TestSuite;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\Fakes\FakeCaptcha;
use Tests\Support\Fakes\FakeFinanceDb;
use Tests\Support\Fakes\FakeKeycloakClient;
use Tests\Support\Fakes\FakeYoutube;
use Tests\Support\TestApplication;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests run against a real database (see tests/README.md). RefreshDatabase migrates it once per run and then
| wraps each test in a transaction.
|
*/

pest()->extend(Tests\TestCase::class)->use(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

/**
 * Create an active user in the given group: member, associate, committee, staff or super_admin. "archived" creates an
 * archived member.
 *
 * This doesn't use User::create(), as that also creates the user in Keycloak and emails them.
 *
 * @param array<string, mixed> $attributes
 */
function createUser(string $group = 'member', array $attributes = []): User
{
    $factory = $group === 'archived' ? UserFactory::new()->member()->archived() : UserFactory::new()->inGroup($group);

    return $factory->createOne($attributes);
}

/**
 * Log in as a new user in the given group (see createUser()). "guest" doesn't log in, and returns null.
 *
 * This uses actingAs(), which doesn't check the account's status on each request like a real session does. So an
 * archived user stays logged in; to test the status check, log them in through the session instead.
 *
 * @param array<string, mixed> $attributes
 */
function loginAs(string $group = 'member', array $attributes = []): ?User
{
    if ($group === 'guest') {
        return null;
    }

    $user = createUser($group, $attributes);
    actingAs($user);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

/**
 * Commit the test's data instead of rolling it back at the end of the test. MariaDB's FULLTEXT indexes only see data
 * that has been committed, so the resources search needs this. Every table except the user groups is emptied after
 * the test.
 */
function commitTestData(): void
{
    $test = TestSuite::getInstance()->test;
    assert($test instanceof Tests\TestCase);

    $test->commitTestData();
}

/*
|--------------------------------------------------------------------------
| Requests
|--------------------------------------------------------------------------
*/

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

/**
 * Make a GET request in the same way as the site's JavaScript.
 *
 * @return TestResponse<Response>
 */
function getAjax(string $uri): TestResponse
{
    return get($uri, ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
}

/**
 * Check who the response let in. $access is one of:
 *  - "login": a redirect to the login page
 *  - an HTTP status code, eg 200, 401 (a guest making an AJAX request) or 403
 *
 * @param TestResponse<Response> $response
 *
 * @return TestResponse<Response>
 */
function expectAccess(TestResponse $response, string|int $access): TestResponse
{
    return match (true) {
        $access === 'login' => $response->assertRedirect(route('auth.login')),
        is_int($access) => $response->assertStatus($access),
        default => throw new InvalidArgumentException("Unknown access \"{$access}\""),
    };
}

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
|
| v4 shows its messages ("Event created" and so on) by flashing them to the session.
|
*/

/**
 * Get the messages flashed to the session.
 *
 * @return list<array{level: string, title: string|null, message: string}>
 */
function notifications(): array
{
    /** @var list<Notification> $notifications */
    $notifications = session('notifications', []);

    return array_map(function (Notification $notification) {
        $notification = $notification->toArray();

        return [
            'level' => $notification['level'],
            'title' => $notification['title'],
            'message' => $notification['message'],
        ];
    }, $notifications);
}

/**
 * Check that a message was flashed to the session.
 */
function expectNotification(string $level, string $message, ?string $title = null): void
{
    expect(notifications())->toContain(['level' => $level, 'title' => $title, 'message' => $message]);
}

/*
|--------------------------------------------------------------------------
| Fakes
|--------------------------------------------------------------------------
|
| Every test gets fakes of the services v4 calls over the network (see TestCase).
|
*/

function fakeKeycloak(): FakeKeycloakClient
{
    $keycloak = app(KeycloakClient::class);
    assert($keycloak instanceof FakeKeycloakClient);

    return $keycloak;
}

function fakeYoutube(): FakeYoutube
{
    $youtube = Youtube::getFacadeRoot();
    assert($youtube instanceof FakeYoutube);

    return $youtube;
}

function fakeCaptcha(): FakeCaptcha
{
    $captcha = NoCaptcha::getFacadeRoot();
    assert($captcha instanceof FakeCaptcha);

    return $captcha;
}

function fakeFinanceDb(): FakeFinanceDb
{
    $financeDb = app(FinanceDb::class);
    assert($financeDb instanceof FakeFinanceDb);

    return $financeDb;
}

/*
|--------------------------------------------------------------------------
| Files
|--------------------------------------------------------------------------
*/

/**
 * Get the path of an upload directory, or a file inside it, relative to the base path (eg "resources/elections/1").
 * The tests keep these in a temporary directory (see TestApplication).
 */
function uploadPath(string $path = ''): string
{
    $app = app();
    assert($app instanceof TestApplication);

    return $app->uploadPath($path);
}
