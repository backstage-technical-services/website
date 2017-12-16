<?php
    Route::group([
        'prefix' => 'page',
    ], function () {
        Route::get('', [
            'as'   => 'page.index',
            'uses' => 'PageController@index',
        ]);
        Route::get('create', [
            'as'   => 'page.create',
            'uses' => 'PageController@create',
        ]);
        Route::post('', [
            'as'   => 'page.store',
            'uses' => 'PageController@store',
        ]);
        Route::group([
            'prefix' => '{slug}',
            'where'  => [
                'slug' => '[\w-]+',
            ],
        ], function () {
            Route::get('', [
                'as'   => 'page.show',
                'uses' => 'PageController@show',
            ]);
            Route::get('edit', [
                'as'   => 'page.edit',
                'uses' => 'PageController@edit',
            ]);
            Route::post('edit', [
                'as'   => 'page.update',
                'uses' => 'PageController@update',
            ]);
            Route::get('delete', [
                'as'   => 'page.destroy',
                'uses' => 'PageController@destroy',
            ]);
        });
    });