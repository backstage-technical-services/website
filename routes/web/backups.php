<?php

use Illuminate\Support\Facades\Route;

// View all backups
Route::get('/backups', 'BackupController@index')
     ->middleware('can:admin')
     ->name('backup.index');

// Create a backup
Route::post('/backups/create/{type}', 'BackupController@store')
     ->where(['type' => 'db|full'])
     ->middleware('can:admin')
     ->name('backup.store');

// Download a backup
Route::get('/backups/{filename}', 'BackupController@download')
     ->where(['filename' => '[A-Za-z0-9_\-\.]+'])
     ->middleware('can:admin')
     ->name('backup.download');

// Delete a backup
Route::post('/backups/{filename}', 'BackupController@destroy')
     ->where(['filename' => '[A-Za-z0-9_\-\.]+'])
     ->middleware('can:admin')
     ->name('backup.destroy');