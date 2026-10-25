<?php

namespace Tests\Support;

use Illuminate\Foundation\Application;

/**
 * The application the tests run. It moves the directories that v4 keeps uploaded files in into a temporary directory,
 * so the tests never read or write real files. v4 builds these paths itself (eg, `base_path('resources/elections')`),
 * so they have to be redirected here rather than through a filesystem disk.
 */
class TestApplication extends Application
{
    /**
     * The directories that are moved, relative to the base path: every directory that v4 writes files to.
     */
    public const UPLOAD_DIRECTORIES = [
        'public',
        'resources/breakages',
        'resources/elections',
        'resources/resources',
        'storage/app',
        'storage/temp',
    ];

    /**
     * The directories that are EFS volumes in production, so always exist.
     */
    public const VOLUMES = [
        'public/images/profiles',
        'resources/breakages',
        'resources/elections',
        'resources/resources',
        'storage/app/backups',
    ];

    public function __construct(string $basePath, public readonly string $uploadsPath)
    {
        parent::__construct($basePath);
    }

    /**
     * Get the path to an upload directory (or a file inside it) in the temporary directory.
     */
    public function uploadPath(string $path = ''): string
    {
        return $this->joinPaths($this->uploadsPath, $path);
    }

    public function basePath($path = '')
    {
        foreach (self::UPLOAD_DIRECTORIES as $directory) {
            if ($path === $directory || str_starts_with($path, $directory . '/')) {
                return $this->uploadPath($path);
            }
        }

        return parent::basePath($path);
    }

    public function resourcePath($path = '')
    {
        return $this->basePath($this->joinPaths('resources', $path));
    }

    public function storagePath($path = '')
    {
        $relativePath = $this->joinPaths('storage', $path);
        foreach (self::UPLOAD_DIRECTORIES as $directory) {
            if ($relativePath === $directory || str_starts_with($relativePath, $directory . '/')) {
                return $this->uploadPath($relativePath);
            }
        }

        return parent::storagePath($path);
    }
}
