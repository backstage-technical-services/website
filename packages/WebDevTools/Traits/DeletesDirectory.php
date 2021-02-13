<?php

namespace Package\WebDevTools\Traits;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait DeletesDirectory
{
    /**
     * Recursively delete a directory and all the files within.
     *
     * @param string $dir
     *
     * @return bool
     */
    public function rmdir($dir)
    {
        if (file_exists($dir) === false) {
            return false;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                if (rmdir($fileinfo->getRealPath()) === false) {
                    return false;
                }
            } else {
                if (unlink($fileinfo->getRealPath()) === false) {
                    return false;
                }
            }
        }

        return rmdir($dir);
    }
}
