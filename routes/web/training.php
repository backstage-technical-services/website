<?php
Route::group([
    'prefix' => 'training',
], function () {
    // Dashboard
    Route::get('', [
        'as'   => 'training.dash',
        'uses' => function () {
            return redirect()->route('training.skill.index');
        },
    ]);

    // Categories
    Route::group([
        'prefix' => 'categories',
    ], function () {
        Route::get('', [
            'as'   => 'training.category.index',
            'uses' => 'Training\CategoryController@index',
        ]);
        Route::post('', [
            'as'   => 'training.category.store',
            'uses' => 'Training\CategoryController@store',
        ]);
        Route::group([
            'prefix' => '{id}',
            'where'  => ['id' => '\d+'],
        ], function () {
            Route::post('', [
                'as'   => 'training.category.update',
                'uses' => 'Training\CategoryController@update',
            ]);
            Route::post('delete', [
                'as'   => 'training.category.destroy',
                'uses' => 'Training\CategoryController@destroy',
            ]);
        });
    });

    // Skills
    Route::group([
        'prefix' => 'skills',
    ], function () {
        Route::get('', [
            'as'   => 'training.skill.index',
            'uses' => 'Training\Skills\SkillController@index',
        ]);
        Route::get('log', [
            'as'   => 'training.skill.log',
            'uses' => 'Training\Skills\SkillController@log',
        ]);
        Route::group([
            'prefix' => 'create',
        ], function () {
            Route::get('', [
                'as'   => 'training.skill.create',
                'uses' => 'Training\Skills\SkillController@create',
            ]);
            Route::post('', [
                'as'   => 'training.skill.store',
                'uses' => 'Training\Skills\SkillController@store',
            ]);
        });
        Route::group([
            'prefix' => '{id}',
            'where'  => ['id' => '\d+'],
        ], function () {
            Route::get('', [
                'as'   => 'training.skill.view',
                'uses' => 'Training\Skills\SkillController@view',
            ]);
            Route::get('edit', [
                'as'   => 'training.skill.edit',
                'uses' => 'Training\Skills\SkillController@edit',
            ]);
            Route::post('', [
                'as'   => 'training.skill.update',
                'uses' => 'Training\Skills\SkillController@update',
            ]);
            Route::post('delete', [
                'as'   => 'training.skill.destroy',
                'uses' => 'Training\Skills\SkillController@destroy',
            ]);
        });
        // Award skill
        Route::group([
            'prefix' => 'award/{id?}',
            'where'  => ['id' => '\d+'],
        ], function () {
            Route::get('', [
                'as'   => 'training.skill.award.form',
                'uses' => 'Training\Skills\AwardedController@awardForm',
            ]);
            Route::post('', [
                'as'   => 'training.skill.award',
                'uses' => 'Training\Skills\AwardedController@award',
            ]);
        });
        // Revoke skill
        Route::group([
            'prefix' => 'revoke/{id?}',
            'where'  => ['id' => '\d+'],
        ], function () {
            Route::get('', [
                'as'   => 'training.skill.revoke.form',
                'uses' => 'Training\Skills\AwardedController@revokeForm',
            ]);
            Route::post('', [
                'as'   => 'training.skill.revoke',
                'uses' => 'Training\Skills\AwardedController@revoke',
            ]);
        });
        // Applications
        Route::group([
            'prefix' => 'apply/{id?}',
            'where'  => ['id' => '\d+'],
        ], function () {
            Route::get('', [
                'as'   => 'training.skill.apply.form',
                'uses' => 'Training\Skills\ApplicationController@form',
            ]);
            Route::post('', [
                'as'   => 'training.skill.apply',
                'uses' => 'Training\Skills\ApplicationController@apply',
            ]);
        });
    });
    // Applications
    Route::group([
        'prefix' => 'applications',
    ], function () {
        Route::get('', [
            'as'   => 'training.skill.application.index',
            'uses' => 'Training\Skills\ApplicationController@index',
        ]);
        Route::group([
            'prefix' => '{id}',
            'where'  => ['id' => '\d+'],
        ], function () {
            Route::get('', [
                'as'   => 'training.skill.application.view',
                'uses' => 'Training\Skills\ApplicationController@view',
            ]);
            Route::post('', [
                'as'   => 'training.skill.application.update',
                'uses' => 'Training\Skills\ApplicationController@update',
            ]);
        });
    });
});