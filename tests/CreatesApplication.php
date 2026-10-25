<?php

namespace Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\Support\TestApplication;
use Tests\Support\TestDatabase;

trait CreatesApplication
{
    /**
     * Create the application in the same way as `bootstrap/app.php`, but as a TestApplication so that uploaded files go
     * to a temporary directory, and connected to the test database.
     */
    public function createApplication(): Application
    {
        $uploadsPath = sys_get_temp_dir() . '/bts-v4-tests/' . Str::random(16);
        $filesystem = new Filesystem();
        foreach ([...TestApplication::UPLOAD_DIRECTORIES, ...TestApplication::VOLUMES] as $directory) {
            $filesystem->ensureDirectoryExists("{$uploadsPath}/{$directory}");
        }

        $app = new TestApplication(realpath(__DIR__ . '/../') ?: '', $uploadsPath);
        $app->singleton(\Illuminate\Contracts\Http\Kernel::class, \App\Http\Kernel::class);
        $app->singleton(Kernel::class, \App\Console\Kernel::class);
        $app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, \App\Exceptions\Handler::class);

        $app->make(Kernel::class)->bootstrap();
        $this->useTestDatabase($app);

        return $app;
    }

    /**
     * Point the database connection at the test database, which the tests start in a container (see TestDatabase).
     * This happens after the configuration has loaded, so not even a cached configuration can point the tests, which
     * wipe the database, at a real one.
     */
    private function useTestDatabase(Application $app): void
    {
        $config = $app->make(Repository::class);
        $name = (string) $config->get('database.default');
        /** @var array<string, mixed> $connection */
        $connection = $config->get("database.connections.{$name}");

        $config->set(
            "database.connections.{$name}",
            array_merge(Arr::except($connection, ['url', 'unix_socket', 'read', 'write']), TestDatabase::connection()),
        );
        $app->make(DatabaseManager::class)->purge($name);
    }
}
