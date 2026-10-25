<?php

namespace Tests;

use Alaouy\Youtube\Facades\Youtube;
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use App\Services\FinanceDb;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Package\Keycloak\KeycloakClient;
use Tests\Support\Fakes\FakeCaptcha;
use Tests\Support\Fakes\FakeFinanceDb;
use Tests\Support\Fakes\FakeKeycloakClient;
use Tests\Support\Fakes\FakeYoutube;
use Tests\Support\TestApplication;
use Tests\Support\UserGroupSeeder;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Seed the user groups after migrating (used by RefreshDatabase).
     *
     * @var class-string
     */
    protected $seeder = UserGroupSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeExternalServices();
        $this->useNoTimezoneOffset();

        $this->beforeApplicationDestroyed(function () {
            if ($this->app instanceof TestApplication) {
                new Filesystem()->deleteDirectory($this->app->uploadsPath);
            }
        });
    }

    /**
     * Commit the test's data instead of rolling it back at the end of the test, and empty every table except the user
     * groups afterwards. See commitTestData() in Pest.php.
     */
    public function commitTestData(): void
    {
        DB::commit();

        $this->beforeApplicationDestroyed(function () {
            Schema::withoutForeignKeyConstraints(function () {
                foreach (Schema::getTableListing() as $table) {
                    if (!in_array($table, ['migrations', 'user_groups'])) {
                        DB::table($table)->truncate();
                    }
                }
            });
        });
    }

    /**
     * The site's JavaScript sends the browser's timezone offset with every request, and v4 shifts some dates by it (see
     * AccountsForTimezones). Without it, v4 falls back to the current UK offset, which changes with the seasons. Using
     * 0 means that dates are stored as they're given.
     */
    private function useNoTimezoneOffset(): void
    {
        $this->withHeader('TZ-OFFSET', '0');
        session(['tz_correction' => 0.0]);
    }

    /**
     * Replace the services that v4 calls over the network with fakes, so no test can reach them. Tests get the fakes
     * with fakeKeycloak(), fakeYoutube(), fakeCaptcha() and fakeFinanceDb() (see Pest.php).
     *
     * Backups run `mysqldump`, which is swapped for tests/Support/bin/mysqldump. It writes a one-line dump, so the
     * tests don't depend on which database client is installed.
     */
    private function fakeExternalServices(): void
    {
        $this->app->instance(KeycloakClient::class, new FakeKeycloakClient());
        $this->app->instance(FinanceDb::class, new FakeFinanceDb());
        Youtube::swap(new FakeYoutube());
        NoCaptcha::swap(new FakeCaptcha());

        $connection = config('database.default');
        config(["database.connections.{$connection}.dump.dump_binary_path" => base_path('tests/Support/bin')]);
    }
}
