# Tests

The tests use [Pest](https://pestphp.com). Most of them are HTTP-level _characterisation_ tests: they pin down what v4
does today, bugs included, so that any change to its behaviour (upgrading Laravel or MariaDB, fixing a bug) is a
deliberate one.

## Running the tests

The feature tests run against a real MariaDB 10.4 database, the same version as production. v4 relies on MariaDB's
non-strict mode, case-insensitive collation and FULLTEXT search, and SQLite would hide all three.

The tests start the database themselves, in a Docker container (with [Testcontainers](https://testcontainers.com)), so
all they need is Docker:

```bash
./vendor/bin/pest
```

Or, to run them in the site container, use `scripts/site.sh test`. The site container uses your Docker through its
socket, so if you started the site before this was set up, run `scripts/site.sh start` again to recreate it.

- The database starts with the first test (which takes about 10 seconds), keeps its data in memory, and is removed when
  the tests finish. The tests point the connection at it after the configuration has loaded, so they can't reach any
  other database, even with a cached configuration.
- If the tests are killed (for example with Ctrl-C), Ryuk, a container that the tests start alongside the
  database, removes the database a few seconds later. Set `TESTCONTAINERS_RYUK_DISABLED=true` to turn it off.
- Use `--filter` to run some of the tests, or `--group` to run a group (see [Known bugs](#known-bugs)).
- `--parallel` doesn't work, as Laravel creates a database for each process and the test user can't.

CI runs `./vendor/bin/pest` in the same way (see `.github/workflows/checks.yml`), so the tests start their own database
there too.

## Structure

- `Feature/<Area>/`: the tests for each area of the site, such as `Feature/Events/`. Each file has its own namespace,
  so that helper functions in different files don't clash.
- `Pest.php`: the helpers that every test can use.
- `Support/`: what the tests run on: the application class, the test database, the fakes and the user groups seeder.
- `Support/bin/`: the fake command-line tools (see [Outside services](#outside-services)).
- `../database/factories/`: a factory for every table (except the join tables, which the factories fill in).
- `../modules/*/tests/`: the tests for each Laravel module, such as the static pages.

## Writing tests

### Users and access

`createUser($group)` creates a user in a group: `member`, `associate`, `committee`, `staff` or `super_admin`.
`archived` creates an archived member. `loginAs($group)` creates one and logs in as them (or doesn't, for `guest`).

v4's groups don't nest: `committee` and `super_admin` are admins, but `super_admin` and `staff` aren't members. So
every route's tests check who can use it:

```php
it('should only let admins see the list of events', function (string $group, string|int $access) {
    loginAs($group);

    expectAccess(get(route('event.index')), $access);
})->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);
```

`expectAccess()` takes `'login'` (a redirect to the login page) or a status code. Where a route needs a login, guests
get a 401 rather than a redirect if they make an AJAX request.

`loginAs()` uses `actingAs()`, which skips the check that a real session does on every request that the account is
still active. To test that check, log in through the session instead.

### Requests

Many endpoints are AJAX-only, as the pages call them with jQuery. Use `postAjax()` and `getAjax()` to make the same
requests. Every request sends a `TZ-OFFSET` header of 0 (see [Dates](#dates-and-timezones)).

### Datasets

Don't use v4's model constants (such as `Event::TYPE_EVENT`) in a dataset. Pest builds datasets when it collects the
tests, so this loads the model early, and PHP then reports the model's deprecation warnings against every test in the
file. Use the value itself, or a closure that returns it.

### Factories

The models don't use `HasFactory`, so use the factories directly:

```php
$event = EventFactory::new()->managedBy($user)->at('2026-11-01 19:00')->createOne();
```

Use `createOne()` rather than `create()`, so that PHPStan knows that it returns a single model. The factories write
the same values as v4 does, including the ones that MariaDB fills in because it isn't in strict mode (such as `''` for
`event_crew.guest_name`).

### Messages and emails

v4 shows its messages ("Event created" and so on) by flashing them to the session. `expectNotification('success',
'Event created')` checks for one, and `notifications()` returns them all.

For emails, use `Mail::fake()` and `Notification::fake()`. The tests use the `sync` queue, so queued emails are sent
straight away (`Mail::assertQueued()` still sees them).

### Outside services

Every test gets fakes of the services that v4 calls over the network, so no test can reach them:

| Service            | Fake                 | Get it with       |
| ------------------ | -------------------- | ----------------- |
| Keycloak admin API | `FakeKeycloakClient` | `fakeKeycloak()`  |
| YouTube API        | `FakeYoutube`        | `fakeYoutube()`   |
| reCAPTCHA          | `FakeCaptcha`        | `fakeCaptcha()`   |
| Finance database   | `FakeFinanceDb`      | `fakeFinanceDb()` |

- reCAPTCHA passes only for `FakeCaptcha::PASS`.
- Backups run `mysqldump`, which is replaced with `Support/bin/mysqldump`. It writes a one-line dump of the database
  (`-- Fake dump of backstage`), so the tests don't depend on which database client is installed.
- v4 only sends events to the finance database in production, so set the environment to `prod` to test that.
- Logging in through Keycloak isn't faked: the tests log in with `loginAs()` instead. To test the login callback itself,
  fake Socialite.

### Files

v4 keeps uploads in directories it builds itself, such as `resources/elections/{id}`. The tests move these, and
`storage/app` and `storage/temp` (where backups are made and kept), into a temporary directory for each test (see
`Support/TestApplication.php`), so they never touch real files. `uploadPath()` gives a path inside it, and the
factories can write files there (for example `IssueFactory::withFile()`).

### Dates and timezones

v4 shifts some dates (event times, election dates) by the browser's timezone offset, which its JavaScript sends with
every request. If the offset is missing, v4 uses the current UK offset, which would make the tests depend on the time
of year. So every request sends an offset of 0, and dates are stored as they're given. Use `travelTo()` to fix "now".

### Known bugs

The tests pin down known bugs too, so that fixing one is a deliberate change. Each of these tests is in the `known-bug`
group and a group for the bug, such as `bug:auto-close`:

```php
it('should leave the crew lists of finished events open, as it updates a column that isn\'t fillable', function () {
    $event = EventFactory::new()->past()->createOne();

    Artisan::call('crew:auto-close');

    assertDatabaseHas('events', ['id' => $event->id, 'crew_list_status' => Event::CREW_LIST_OPEN]);
})->group('known-bug', 'bug:auto-close');
```

When you fix the bug, change the test to match. `./vendor/bin/pest --group=known-bug` runs all of them, and
`./vendor/bin/pest --list-groups` lists the bugs.

### MariaDB behaviour

Tests that depend on how MariaDB behaves (FULLTEXT search, non-strict mode, case-insensitive comparisons) are in the
`mariadb` group.

FULLTEXT indexes only see committed rows, but each test runs in a transaction that's rolled back. So the search tests
call `commitTestData()` first, which commits the test's data and empties the tables after the test.

## What the tests don't cover

- Behaviour that only exists in the browser, such as jQuery validation, modals and live filtering. The tests make the
  requests that the JavaScript would.
- Logging in through Keycloak itself (see [Outside services](#outside-services)).
- How emails look: the tests check who they go to and what they contain.
- Speed: nothing checks how long a page takes or how many queries it makes.
- What the real outside services do with v4's requests: the fakes record the requests, and return what the services
  would.
