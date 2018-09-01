<?php
Route::group([
    'prefix' => 'backups',
], function () {
    Route::get('', [
        'as'   => 'backup.index',
        'uses' => 'BackupController@index',
    ]);
    Route::post('create/{type}', [
        'as'    => 'backup.store',
        'uses'  => 'BackupController@store',
        'where' => ['type' => 'db|full'],
    ]);
    Route::group([
        'prefix' => '{filename}',
        'where'  => ['filename' => '[A-Za-z0-9_\-\.]+'],
    ], function () {
        Route::get('', [
            'as'   => 'backup.download',
            'uses' => 'BackupController@download',
        ]);
        Route::post('delete', [
            'as'   => 'backup.destroy',
            'uses' => 'BackupController@destroy',
        ]);
    });
});