<?php
Route::group(
    [
        'prefix' => 'equipment',
    ],
    function () {
        // Asset Register
        Route::get('assets', [
            'as' => 'equipment.assets',
            'uses' => 'Equipment\AssetController@view',
        ]);
        // Repairs database
        Route::group(
            [
                'prefix' => 'repairs',
            ],
            function () {
                Route::get('', [
                    'as' => 'equipment.repairs.index',
                    'uses' => 'Equipment\RepairsController@index',
                ]);
                Route::get('create', [
                    'as' => 'equipment.repairs.create',
                    'uses' => 'Equipment\RepairsController@create',
                ]);
                Route::post('', [
                    'as' => 'equipment.repairs.store',
                    'uses' => 'Equipment\RepairsController@store',
                ]);
                Route::group(
                    [
                        'prefix' => '{id}',
                        'where' => ['id' => '[\d]+'],
                    ],
                    function () {
                        Route::get('', [
                            'as' => 'equipment.repairs.view',
                            'uses' => 'Equipment\RepairsController@view',
                        ]);
                        Route::group(
                            [
                                'prefix' => 'images',
                            ],
                            function () {
                                Route::get('{imageId}', [
                                    'as' => 'equipment.repairs.images.stream',
                                    'uses' => 'Equipment\RepairsController@streamImage',
                                    'where' => ['imageId' => '[\d]+'],
                                ]);
                            },
                        );
                        Route::post('', [
                            'as' => 'equipment.repairs.update',
                            'uses' => 'Equipment\RepairsController@update',
                        ]);
                    },
                );
            },
        );
    },
);
