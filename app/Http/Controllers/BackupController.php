<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use bnjns\LaravelNotifications\NotificationHandler;
use bnjns\WebDevTools\Laravel\Traits\UsesAjax;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    use UsesAjax;
    
    /** @var BackupService $service */
    private $service;
    
    /** @var NotificationHandler $notify */
    private $notify;
    
    /**
     * BackupController constructor.
     *
     * @param  BackupService        $service
     * @param  NotificationHandler  $notify
     */
    public function __construct(BackupService $service, NotificationHandler $notify)
    {
        $this->middleware('auth');
        $this->service = $service;
        $this->notify  = $notify;
    }
    
    /**
     * View the list of existing backup files.
     *
     * @return View
     */
    public function index()
    {
        $backups = $this->service->viewAllBackups();
    
        return view('backups.index')->with(compact('backups'));
    }
    
    /**
     * Download a site backup.
     *
     * @param  string  $filename
     *
     * @return BinaryFileResponse
     */
    public function download(string $filename)
    {
        $file = $this->service->getFile($filename);
        
        return response()->download($file, $file->getFilename());
    }
    
    /**
     * Create a new backup.
     *
     * @param  string  $type  The type of backup to create.
     *
     * @return JsonResponse
     */
    public function store(string $type)
    {
        $this->requireAjax();
    
        $this->service->runBackup($type);
    
        $this->notify->success('Backup created');
        return response()->json();
    }
    
    /**
     * Delete a backup.
     *
     * @param  string  $filename
     *
     * @return JsonResponse
     */
    public function destroy(string $filename)
    {
        $this->requireAjax();
    
        $this->service->delete($filename);
    
        $this->notify->success('Backup deleted');
        return response()->json();
    }
}
