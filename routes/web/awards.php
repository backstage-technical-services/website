<?php
Route::group(
    [
        'prefix' => 'awards',
    ],
    function () {
        // Awards
        Route::get('', [
            'as' => 'award.index',
            'uses' => 'Awards\AwardController@index',
        ]);
        Route::post('', [
            'as' => 'award.store',
            'uses' => 'Awards\AwardController@store',
        ]);
        // Update
        Route::group(
            [
                'prefix' => '{id}',
                'where' => [
                    'id' => '[\d]+',
                ],
            ],
            function () {
                Route::post('', [
                    'as' => 'award.update',
                    'uses' => 'Awards\AwardController@update',
                ]);
                Route::post('approve', [
                    'as' => 'award.approve',
                    'uses' => 'Awards\AwardController@approve',
                ]);
                Route::post('delete', [
                    'as' => 'award.destroy',
                    'uses' => 'Awards\AwardController@destroy',
                ]);
            },
        );
        // Propose
        Route::post('suggest', [
            'as' => 'award.suggest',
            'uses' => 'Awards\AwardController@suggest',
        ]);

        // Award Season
        Route::group(
            [
                'prefix' => 'seasons',
            ],
            function () {
                Route::get('', [
                    'as' => 'award.season.index',
                    'uses' => 'Awards\SeasonController@index',
                ]);
                Route::post('', [
                    'as' => 'award.season.store',
                    'uses' => 'Awards\SeasonController@store',
                ]);
                Route::group(
                    [
                        'prefix' => '{id}',
                        'where' => [
                            'id' => '[\d]+',
                        ],
                    ],
                    function () {
                        Route::get('', [
                            'as' => 'award.season.view',
                            'uses' => 'Awards\SeasonController@view',
                        ]);
                        Route::post('', [
                            'as' => 'award.season.update',
                            'uses' => 'Awards\SeasonController@update',
                        ]);
                        Route::post('status', [
                            'as' => 'award.season.status',
                            'uses' => 'Awards\SeasonController@updateStatus',
                        ]);
                        Route::post('vote', [
                            'as' => 'award.season.vote',
                            'uses' => 'Awards\SeasonController@vote',
                        ]);
                        Route::post('delete', [
                            'as' => 'award.season.destroy',
                            'uses' => 'Awards\SeasonController@destroy',
                        ]);

                        // Nominations
                        Route::group(
                            [
                                'prefix' => 'nominations',
                            ],
                            function () {
                                Route::get('', [
                                    'as' => 'award.season.nomination.index',
                                    'uses' => 'Awards\NominationController@index',
                                ]);
                                Route::post('', [
                                    'as' => 'award.season.nomination.store',
                                    'uses' => 'Awards\NominationController@store',
                                ]);
                                Route::group(
                                    [
                                        'prefix' => '{nominationId}',
                                        'where' => [
                                            'id' => '[\d]+',
                                        ],
                                    ],
                                    function () {
                                        Route::post('approve', [
                                            'as' => 'award.season.nomination.approve',
                                            'uses' => 'Awards\NominationController@approve',
                                        ]);
                                        Route::post('delete', [
                                            'as' => 'award.season.nomination.destroy',
                                            'uses' => 'Awards\NominationController@destroy',
                                        ]);
                                    },
                                );
                            },
                        );
                    },
                );
            },
        );
    },
);
