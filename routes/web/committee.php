<?php
Route::group(
    [
        'prefix' => 'committee',
    ],
    function () {
        // View
        Route::get('', [
            'as' => 'committee.view',
            'uses' => 'CommitteeController@view',
        ]);
        // Add
        Route::post('add', [
            'as' => 'committee.add',
            'uses' => 'CommitteeController@store',
        ]);
        // Edit
        Route::post('edit', [
            'as' => 'committee.edit',
            'uses' => 'CommitteeController@update',
        ]);
        // Delete
        Route::post('delete', [
            'as' => 'committee.delete',
            'uses' => 'CommitteeController@destroy',
        ]);
    },
);
