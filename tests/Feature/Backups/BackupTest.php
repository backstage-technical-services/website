<?php

namespace Tests\Feature\Backups\Backup;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Admins can list, create, download and delete backups of the site. They're kept in storage/app/backups, which is an
 * EFS volume in production. A database backup is a .sql file, and a full backup is a .zip of the database and the
 * uploaded files. The database is dumped by the fake mysqldump in tests/Support/bin.
 */

function addBackup(string $filename, string $contents = '-- backup'): string
{
    $path = uploadPath("storage/app/backups/{$filename}");
    File::put($path, $contents);

    return $path;
}

/**
 * @return list<SplFileInfo>
 */
function backups(): array
{
    return array_values(File::files(uploadPath('storage/app/backups')));
}

/**
 * @return list<string>
 */
function zipEntries(string $path): array
{
    $zip = new ZipArchive();
    $zip->open($path);
    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entries[] = (string) $zip->getNameIndex($i);
    }
    $zip->close();
    sort($entries);

    return $entries;
}

describe('the list of backups', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('backup.index')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list the database and full backups, with their sizes', function () {
        addBackup('2026-10-24_00-00-00.sql', str_repeat('x', 1536));
        addBackup('2026-10-24-00-00-00.zip', str_repeat('x', 100));
        addBackup('notes.txt');
        loginAs('committee');

        get(route('backup.index'))
            ->assertOk()
            ->assertSee('title="Database only"', false)
            ->assertSee('title="Database and resources"', false)
            ->assertSeeText('2 kB')
            ->assertSeeText('100 B')
            ->assertSee(route('backup.download', ['filename' => '2026-10-24_00-00-00.sql']), false)
            ->assertSee(route('backup.destroy', ['filename' => '2026-10-24-00-00-00.zip']), false)
            ->assertDontSee('notes.txt');
    });

    it('should list the newest backups first', function () {
        addBackup('a.sql');
        sleep(1);
        addBackup('b.sql');
        loginAs('committee');

        get(route('backup.index'))->assertSeeInOrder(
            [route('backup.download', ['filename' => 'b.sql']), route('backup.download', ['filename' => 'a.sql'])],
            false,
        );
    });

    it('should say if there are no backups', function () {
        loginAs('committee');

        get(route('backup.index'))->assertOk()->assertSeeText('No backups');
    });

    it('should break if a backup is empty', function () {
        addBackup('empty.sql', '');
        loginAs('committee');

        get(route('backup.index'))->assertServerError();
    })->group('known-bug', 'bug:empty-backup-crash');

    it('should say that backups are made automatically, but not schedule any', function () {
        loginAs('committee');

        get(route('backup.index'))->assertSeeText(
            'Database backups are generated daily, and full site backups are generated weekly.',
        );

        $commands = collect(app(Schedule::class)->events())->map(fn(Event $event) => (string) $event->command);
        expect($commands)->toHaveCount(1);
        expect($commands->first())->toEndWith('crew:auto-close');
    })->group('known-bug', 'bug:backups-not-scheduled');
});

describe('creating a backup', function () {
    it('should only let admins create backups', function (string $group, int $status) {
        loginAs($group);

        postAjax(route('backup.store', ['type' => 'db']))->assertStatus($status);

        expect(backups())->toHaveCount($status === 200 ? 1 : 0);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should back up the database', function () {
        loginAs('committee');

        postAjax(route('backup.store', ['type' => 'db']))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        expect(backups())->toHaveCount(1);
        expect(backups()[0]->getFilename())->toMatch('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/');
        expect(backups()[0]->getContents())->toBe(
            '-- Fake dump of ' . config('database.connections.mysql.database') . "\n",
        );
        expectNotification('success', 'Backup created');
    });

    it('should back up the database and the uploaded files, under their full paths', function () {
        File::put(uploadPath('public/images/profiles/1.jpg'), 'avatar');
        File::put(uploadPath('resources/resources/1.pdf'), 'resource');
        File::put(uploadPath('resources/breakages/1.jpg'), 'breakage');
        File::ensureDirectoryExists(uploadPath('resources/elections/1'));
        File::put(uploadPath('resources/elections/1/2.pdf'), 'manifesto');
        loginAs('committee');

        postAjax(route('backup.store', ['type' => 'full']))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        expect(backups())->toHaveCount(1);
        expect(backups()[0]->getFilename())->toMatch('/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.zip$/');
        $root = ltrim(uploadPath(), '/');
        expect(zipEntries(backups()[0]->getPathname()))->toBe([
            'db-dumps/mysql-' . config('database.connections.mysql.database') . '.sql',
            "{$root}/public/images/profiles/1.jpg",
            "{$root}/resources/breakages/1.jpg",
            "{$root}/resources/elections/1/",
            "{$root}/resources/elections/1/2.pdf",
            "{$root}/resources/resources/1.pdf",
        ]);
        expectNotification('success', 'Backup created');
    });

    it('should break if the database can\'t be backed up', function () {
        config(['database.connections.mysql.dump.dump_binary_path' => uploadPath('missing')]);
        loginAs('committee');

        postAjax(route('backup.store', ['type' => 'db']))->assertServerError();

        expect(backups())->toBeEmpty();
    });

    it('should say a full backup was created even if it failed', function () {
        config(['database.connections.mysql.dump.dump_binary_path' => uploadPath('missing')]);
        loginAs('committee');

        postAjax(route('backup.store', ['type' => 'full']))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        expect(backups())->toBeEmpty();
        expectNotification('success', 'Backup created');
    })->group('known-bug', 'bug:failed-backup-success');

    it('should only create database or full backups', function () {
        loginAs('committee');

        postAjax(route('backup.store', ['type' => 'files']))->assertNotFound();
    });

    it('should only accept AJAX requests', function () {
        loginAs('committee');

        post(route('backup.store', ['type' => 'db']))->assertNotFound();

        expect(backups())->toBeEmpty();
    });
});

describe('downloading a backup', function () {
    it('should only let admins download backups', function (string $group, string|int $access) {
        addBackup('2026-10-24_00-00-00.sql');
        loginAs($group);

        expectAccess(get(route('backup.download', ['filename' => '2026-10-24_00-00-00.sql'])), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should download the backup', function () {
        addBackup('2026-10-24_00-00-00.sql', '-- the backup');
        loginAs('committee');

        $response = get(route('backup.download', ['filename' => '2026-10-24_00-00-00.sql']))->assertDownload(
            '2026-10-24_00-00-00.sql',
        );

        assert($response->baseResponse instanceof BinaryFileResponse);
        expect($response->baseResponse->getFile()->getContent())->toBe('-- the backup');
    });

    it('should download any file in the backups directory', function () {
        addBackup('notes.txt', 'notes');
        loginAs('committee');

        get(route('backup.download', ['filename' => 'notes.txt']))->assertDownload('notes.txt');
    });

    it('should give a 404 for a missing backup', function (string $filename) {
        loginAs('committee');

        get(route('backup.download', ['filename' => $filename]))->assertNotFound();
    })->with(['missing.sql', '..']);

    it('should not download files outside the backups directory', function () {
        loginAs('committee');

        get('/backups/..%2F..%2F.env')->assertNotFound();
        get('/backups/../../.env')->assertNotFound();
    });
});

describe('deleting a backup', function () {
    it('should only let admins delete backups', function (string $group, int $status) {
        $path = addBackup('2026-10-24_00-00-00.sql');
        loginAs($group);

        postAjax(route('backup.destroy', ['filename' => '2026-10-24_00-00-00.sql']))->assertStatus($status);

        expect(file_exists($path))->toBe($status !== 200);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the backup', function () {
        $path = addBackup('2026-10-24_00-00-00.sql');
        loginAs('committee');

        postAjax(route('backup.destroy', ['filename' => '2026-10-24_00-00-00.sql']))
            ->assertOk()
            ->assertExactJson(['response' => true]);

        expect($path)->not->toBeFile();
        expectNotification('success', 'Backup deleted');
    });

    it('should say if the backup can\'t be found', function () {
        loginAs('committee');

        postAjax(route('backup.destroy', ['filename' => 'missing.sql']))
            ->assertNotFound()
            ->assertJson(['error' => 'Unknown backup']);
    });

    it('should break when asked to delete the parent directory', function () {
        loginAs('committee');

        postAjax(route('backup.destroy', ['filename' => '..']))->assertServerError();

        expect(uploadPath('storage/app'))->toBeDirectory();
    })->group('known-bug', 'bug:backup-delete-directory');

    it('should only accept AJAX requests', function () {
        $path = addBackup('2026-10-24_00-00-00.sql');
        loginAs('committee');

        post(route('backup.destroy', ['filename' => '2026-10-24_00-00-00.sql']))->assertNotFound();

        expect($path)->toBeFile();
    });
});

describe('the backup:db command', function () {
    it('should back up the database and say where to', function () {
        expect(Artisan::call('backup:db'))->toBe(0);
        expect(Artisan::output())->toContain("Database backed up to '" . uploadPath('storage/app/backups/'));

        expect(backups())->toHaveCount(1);
    });
});
