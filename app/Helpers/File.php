<?php

namespace App\Helpers;

use SplFileInfo;

class File extends SplFileInfo
{
    /**
     * Get the size of the file as a human readable string.
     *
     * @return string
     */
    public function getSizeForHumans()
    {
        $bytes = $this->getSize();
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), [0, 0, 2, 2, 3][$i]) . ' ' . ['B', 'kB', 'MB', 'GB', 'TB'][$i];
    }
}
