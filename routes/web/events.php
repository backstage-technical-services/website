<?php
Route::group([
    'prefix' => 'events',
], function () {
    Route::get('', [
        'as'   => 'event.index',
        'uses' => 'Events\EventController@index',
    ]);
    Route::get('diary/{year?}/{month?}', [
        'as'   => 'event.diary',
        'uses' => 'Events\DiaryController@view',
    ])->where('year', '[\d]{4}')
         ->where('month', '[\d]{1,2}');
    Route::get('create', [
        'as'   => 'event.create',
        'uses' => 'Events\EventController@create',
    ]);
    Route::post('create', [
        'as'   => 'event.store',
        'uses' => 'Events\EventController@store',
    ]);
    Route::get('export', [
        'as'   => 'event.export',
        'uses' => 'Events\DiaryController@export',
    ]);
    Route::post('search', [
        'as'   => 'event.search',
        'uses' => 'Events\EventController@search',
    ]);
    Route::get('report/{id?}', [
        'as'   => 'event.report',
        'uses' => 'Events\EventController@report',
    ])->where('id', '[\d]+');

    // Individual Events
    Route::group([
        'prefix' => '{id}',
        'where'  => ['id' => '[\d]+'],
    ], function () {
        // Events
        Route::get('', [
            'as'   => 'event.view',
            'uses' => 'Events\EventController@view',
        ]);
        Route::post('', [
            'as'   => 'event.update',
            'uses' => 'Events\EventController@update',
        ]);
        Route::post('delete', [
            'as'   => 'event.destroy',
            'uses' => 'Events\EventController@destroy',
        ]);
        // Event crew
        Route::group([
            'prefix' => 'crew',
        ], function () {
            Route::post('volunteer', [
                'as'   => 'event.volunteer',
                'uses' => 'Events\CrewController@toggleVolunteer',
            ]);
            Route::post('', [
                'as'   => 'event.crew.store',
                'uses' => 'Events\CrewController@store',
            ]);
            Route::post('{crewId}', [
                'as'   => 'event.crew.update',
                'uses' => 'Events\CrewController@update',
            ])->where('crewId', '[\d]+');
            Route::post('{crewId}/delete', [
                'as'   => 'event.crew.destroy',
                'uses' => 'Events\CrewController@destroy',
            ])->where('crewId', '[\d]+');
        });
        // Event times
        Route::group([
            'prefix' => 'times',
        ], function () {
            Route::post('', [
                'as'   => 'event.time.store',
                'uses' => 'Events\TimeController@store',
            ]);
            Route::post('{timeId}', [
                'as'   => 'event.time.update',
                'uses' => 'Events\TimeController@update',
            ])->where('timeId', '[\d]+');
            Route::post('{timeId}/delete', [
                'as'   => 'event.time.destroy',
                'uses' => 'Events\TimeController@destroy',
            ])->where('timeId', '[\d]+');
        });
        // Event emails
        Route::post('email', [
            'as'   => 'event.email.store',
            'uses' => 'Events\EmailController@store',
        ]);
        Route::get('finance-email', [
            'uses' => 'Events\EventController@sendFinanceEmail',
        ]);
    });

    // Event Paperwork
    Route::group([
        'prefix' => 'paperwork',
    ], function () {
        Route::get('', [
            'as'   => 'event.paperwork',
            'uses' => 'Events\PaperworkController@index',
        ]);
        Route::post('', [
            'as'   => 'event.paperwork.store',
            'uses' => 'Events\PaperworkController@store',
        ]);
        Route::post('{paperworkId}', [
            'as'   => 'event.paperwork.update',
            'uses' => 'Events\PaperworkController@update',
        ])->where('paperworkId', '[\d]+');
        Route::post('{paperworkId}/delete', [
            'as'   => 'event.paperwork.destroy',
            'uses' => 'Events\PaperworkController@destroy',
        ])->where('paperworkId', '[\d]+');
    });
});