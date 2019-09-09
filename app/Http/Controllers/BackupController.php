<?php

namespace App\Http\Controllers;

use App\Console\Commands\BackupDb;
use App\Helpers\File;
use bnjns\LaravelNotifications\Facades\Notify;
use bnjns\WebDevTools\Laravel\Traits\UsesAjax;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zend\Stdlib\Glob;

class BackupController extends Controller
{
    use UsesAjax;

    /**
     * BackupController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the list of existing backup files.
     *
     * @return View
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorizeGate('admin');
    
        $files = Glob::glob(storage_path('app/backups/') . '*.{zip,sql}', Glob::GLOB_BRACE);
        array_multisort(
            array_map('filectime', $files),
            SORT_NUMERIC,
            SORT_DESC,
            $files
        );

        return view('backups.index')->with([
            'backups' => array_map(function ($filename) {
                return new File($filename);
            }, $files),
        ]);
    }

    /**
     * Download a site backup.
     *
     * @param $filename
     *
     * @return BinaryFileResponse
     * @throws AuthorizationException
     */
    public function download($filename)
    {
        $this->authorizeGate('admin');

        $file = new File(storage_path('app/backups/' . $filename));

        if (!$file->isFile()) {
            throw new NotFoundHttpException();
        }

        return response()->download($file, $file->getFilename());
    }

    /**
     * Create a new backup.
     *
     * @param string $type The type of backup to create.
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store($type)
    {
        $this->authorizeGate('admin');
        $this->requireAjax();

        if ($type == 'db') {
            Artisan::call(BackupDb::class);
        } else if ($type == 'full') {
            Artisan::call('backup:run');
        }

        Notify::success('Backup created');
        return $this->ajaxResponse(true);
    }

    /**
     * Delete a backup.
     *
     * @param string $filename
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy($filename)
    {
        $this->authorizeGate('admin');
        $this->requireAjax();

        $file = storage_path('app/backups/' . $filename);

        if (!file_exists($file)) {
            return $this->ajaxError(0, 404, 'Unknown backup');
        }

        unlink($file);

        Notify::success('Backup deleted');
        return $this->ajaxResponse(true);
    }
}
