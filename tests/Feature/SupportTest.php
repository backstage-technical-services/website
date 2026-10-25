<?php

namespace Tests\Feature;

use App\Models\Resources\Resource;
use Database\Factories\Resources\ResourceFactory;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;

/*
 * Tests of the test set-up itself.
 */

it('should keep uploads in a temporary directory', function (string $path) {
    expect(str_starts_with(uploadPath(), sys_get_temp_dir()))->toBeTrue();
    expect(base_path($path))->toBe(uploadPath($path))->toBeDirectory();
})->with(['public/images/profiles', 'resources/breakages', 'resources/elections', 'resources/resources']);

it('should keep backups in a temporary directory', function () {
    expect(storage_path('app/backups'))->toBe(uploadPath('storage/app/backups'))->toBeDirectory();
});

it('should leave the views where they are', function () {
    expect(resource_path('views'))->toBe(realpath(__DIR__ . '/../../resources/views'));
});

it('should run against MariaDB, like production', function () {
    expect(DB::connection()->getDriverName())->toBe('mysql');
    expect(DB::selectOne('SELECT VERSION() AS version')?->version)->toContain('MariaDB');
})->group('mariadb');

it('should not find uncommitted rows with full-text search', function () {
    ResourceFactory::new()->createOne(['title' => 'Lighting rig plan']);

    expect(Resource::search('lighting')->count())->toBe(0);
})->group('mariadb');

it('should find committed rows with full-text search', function () {
    commitTestData();
    ResourceFactory::new()->createOne(['title' => 'Lighting rig plan']);

    expect(Resource::search('lighting')->count())->toBe(1);
})->group('mariadb');

it('should empty the tables after committing test data', function () {
    assertDatabaseCount('resources', 0);
    assertDatabaseCount('user_groups', 5);
});
