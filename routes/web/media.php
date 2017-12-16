<?php
    Route::group([
        'prefix' => 'media',
    ], function () {
        // Images
        Route::group([
            'prefix' => 'images',
        ], function () {
            Route::get('', [
                'as'   => 'media.images.index',
                'uses' => 'Media\ImageController@index',
            ]);
            Route::get('album/{id}', [
                'as'   => 'media.images.album',
                'uses' => 'Media\ImageController@album',
            ]);
        });
        // Videos
        Route::group([
            'prefix' => 'videos',
        ], function () {
            Route::get('', [
                'as'   => 'media.videos.index',
                'uses' => 'Media\VideoController@index',
            ]);
            Route::get('{id}', [
                'as'   => 'media.videos.show',
                'uses' => 'Media\VideoController@show',
            ]);
        });
    });