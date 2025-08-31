<?php
Route::group(
    [
        'prefix' => 'media',
    ],
    function () {
        // Images
        Route::get('images', function () {
            return redirect(config('bts.links.instagram'));
        });
        // Videos
        Route::group(
            [
                'prefix' => 'videos',
            ],
            function () {
                Route::get('', [
                    'as' => 'media.videos.index',
                    'uses' => 'Media\VideoController@index',
                ]);
                Route::get('{id}', [
                    'as' => 'media.videos.show',
                    'uses' => 'Media\VideoController@show',
                ]);
            },
        );
    },
);
