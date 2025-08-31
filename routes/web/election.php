<?php
Route::group(
    [
        'prefix' => 'elections',
    ],
    function () {
        // Index
        Route::get('', [
            'as' => 'election.index',
            'uses' => 'Elections\ElectionController@index',
        ]);
        // Create
        Route::get('create', [
            'as' => 'election.create',
            'uses' => 'Elections\ElectionController@create',
        ]);
        Route::post('create', [
            'as' => 'election.store',
            'uses' => 'Elections\ElectionController@store',
        ]);
        // Single election routes
        Route::group(
            [
                'prefix' => '{id}',
                'where' => ['id' => '[\d]+'],
            ],
            function () {
                // View
                Route::get('', [
                    'as' => 'election.view',
                    'uses' => 'Elections\ElectionController@view',
                ]);
                // Edit
                Route::get('edit', [
                    'as' => 'election.edit',
                    'uses' => 'Elections\ElectionController@edit',
                ]);
                Route::post('edit', [
                    'as' => 'election.update',
                    'uses' => 'Elections\ElectionController@update',
                ]);
                // Delete
                Route::post('delete', [
                    'as' => 'election.destroy',
                    'uses' => 'Elections\ElectionController@destroy',
                ]);
                // Elect
                Route::post('elect', [
                    'as' => 'election.elect',
                    'uses' => 'Elections\ElectionController@elect',
                ]);
                // Nominate
                Route::post('nominate', [
                    'as' => 'election.nominate',
                    'uses' => 'Elections\NominationController@store',
                ]);
                Route::group(
                    [
                        'prefix' => '{nominationId}',
                        'where' => [
                            'nomineeId' => '[\d]+',
                        ],
                    ],
                    function () {
                        // View manifesto
                        Route::get('manifesto', [
                            'as' => 'election.manifesto',
                            'uses' => 'Elections\NominationController@manifesto',
                        ]);
                        // Remove nomination
                        Route::post('delete', [
                            'as' => 'election.nomination.delete',
                            'uses' => 'Elections\NominationController@destroy',
                        ]);
                    },
                );
            },
        );
    },
);
