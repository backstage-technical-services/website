<?php
Route::group(
    [
        'prefix' => 'users',
    ],
    function () {
        Route::get('', [
            'as' => 'user.index',
            'uses' => 'Members\UsersController@index',
        ]);
        Route::post('', [
            'as' => 'user.update.bulk',
            'uses' => 'Members\UsersController@postIndex',
        ]);
        Route::get('create', [
            'as' => 'user.create',
            'uses' => 'Members\UsersController@create',
        ]);
        Route::get('create/summary', [
            'as' => 'user.create.summary',
            'uses' => 'Members\UsersController@createSummary',
        ]);
        Route::post('create', [
            'as' => 'user.store',
            'uses' => 'Members\UsersController@store',
        ]);
        Route::group(
            [
                'prefix' => '{username}',
                'where' => ['username' => '[\w]+'],
            ],
            function () {
                Route::get('', [
                    'as' => 'user.view',
                    'uses' => 'Members\UsersController@view',
                ]);
                Route::get('edit', [
                    'as' => 'user.edit',
                    'uses' => 'Members\UsersController@edit',
                ]);
                Route::post('', [
                    'as' => 'user.update',
                    'uses' => 'Members\UsersController@update',
                ]);
            },
        );
    },
);
