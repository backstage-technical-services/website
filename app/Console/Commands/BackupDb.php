<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to a file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->callSilent('snapshot:create', [
            'name' => ($name = date('Y-m-d_H-i-s')),
        ]);

        $this->info(
            "Database backed up to '" .
                realpath(config('filesystems.disks.snapshots.root') . '/' . $name . '.sql') .
                "'.",
        );
    }
}
