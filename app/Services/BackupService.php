<?php


namespace App\Services;


use App\Console\Commands\BackupDb;
use App\Helpers\File;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zend\Stdlib\Glob;

class BackupService
{
    private const BACKUP_DIR      = 'app/backups/';
    private const FILE_SORT       = 'filectime';
    private const CMD_FULL_BACKUP = 'backup:run';
    
    /** @var Kernel */
    private $kernel;
    
    /** @var string $backupDir */
    private $backupDir;
    
    /**
     * BackupService constructor.
     *
     * @param  Kernel  $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel    = $kernel;
        $this->backupDir = storage_path(self::BACKUP_DIR);
    }
    
    /**
     * View all the backups with the given file extensions.
     *
     * @param  array  $extensions
     * @return array
     */
    public function viewAllBackups(array $extensions = ['zip', 'sql']): array
    {
        $extensionBrace = implode(',', $extensions);
        $files          = Glob::glob($this->backupDir . '*.{' . $extensionBrace . '}', Glob::GLOB_BRACE);
        
        array_multisort(
            array_map(self::FILE_SORT, $files),
            SORT_NUMERIC,
            SORT_DESC,
            $files
        );
        
        return array_map(function (string $filename) {
            return new File($filename);
        }, $files);
    }
    
    /**
     * Run a backup.
     *
     * @param  string  $type
     */
    public function runBackup(string $type)
    {
        if ($type === 'db') {
            $this->kernel->call(BackupDb::class);
        } else if ($type === 'full') {
            $this->kernel->call(self::CMD_FULL_BACKUP);
        }
    }
    
    /**
     * Delete a backup.
     *
     * @param  string  $filename
     * @throws NotFoundHttpException
     */
    public function delete(string $filename)
    {
        /** @var File $file */
        $file = $this->getFile($filename);
        
        unlink($file->getRealPath());
    }
    
    /**
     * Get a backup.
     *
     * @param  string  $filename
     * @return File
     * @throws NotFoundHttpException
     */
    public function getFile(string $filename): File
    {
        $file = new File(realpath($this->backupDir . $filename));
        
        if (!$file->isFile()) {
            throw new NotFoundHttpException();
        }
        
        return $file;
    }
}