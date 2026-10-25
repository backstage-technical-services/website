<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Support\TestApplication;

trait CreatesApplication
{
    /**
     * Create the application in the same way as `bootstrap/app.php`, but as a TestApplication so that uploaded files go
     * to a temporary directory.
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
        $this->guardAgainstRealDatabase($app);

        return $app;
    }

    /**
     * RefreshDatabase wipes the database, so make sure it isn't a real one before it runs.
     */
    private function guardAgainstRealDatabase(Application $app): void
    {
        $connection = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($database !== ':memory:' && !str_contains($database, 'test')) {
            throw new RuntimeException(
                "The tests wipe the database, so they only run against one with \"test\" in its name (not \"{$database}\").",
            );
        }
    }
}
