<?php
Route::group([
    'prefix' => 'resources',
], function () {
    // Search
    Route::get('', [
        'as'   => 'resource.index',
        'uses' => 'Resources\ResourceController@index',
    ]);
    Route::group([
        'prefix' => 'search',
    ], function () {
        Route::get('', [
            'as'   => 'resource.search',
            'uses' => 'Resources\SearchController@getSearch',
        ]);
        Route::post('', [
            'as'   => 'resource.search.process',
            'uses' => 'Resources\SearchController@postSearch',
        ]);
    });
    // Create
    Route::get('create', [
        'as'   => 'resource.create',
        'uses' => 'Resources\ResourceController@create',
    ]);
    Route::post('create', [
        'as'   => 'resource.store',
        'uses' => 'Resources\ResourceController@store',
    ]);
    Route::group([
        'prefix' => '{id}',
        'where'  => ['id' => '[\d]+'],
    ], function () {
        // View
        Route::get('', [
            'as'   => 'resource.view',
            'uses' => 'Resources\ResourceController@view',
        ]);
        Route::get('view', [
            'as'   => 'resource.stream',
            'uses' => 'Resources\ResourceController@stream',
        ]);
        // Download
        Route::get('download', [
            'as'   => 'resource.download',
            'uses' => 'Resources\ResourceController@download',
        ]);
        // Edit
        Route::get('edit', [
            'as'   => 'resource.edit',
            'uses' => 'Resources\ResourceController@edit',
        ]);
        Route::post('edit', [
            'as'   => 'resource.update',
            'uses' => 'Resources\ResourceController@update',
        ]);
        // Issue
        Route::get('issue', [
            'as'   => 'resource.issue',
            'uses' => 'Resources\ResourceController@issueForm',
        ]);
        Route::post('issue', [
            'as'   => 'resource.issue.submit',
            'uses' => 'Resources\ResourceController@issue',
        ]);
        Route::get('history', [
            'as'   => 'resource.history',
            'uses' => 'Resources\ResourceController@history',
        ]);
        // Delete
        Route::post('delete', [
            'as'   => 'resource.destroy',
            'uses' => 'Resources\ResourceController@destroy',
        ]);
    });
    // Categories
    Route::group([
        'prefix' => 'categories',
    ], function () {
        // List
        Route::get('', [
            'as'   => 'resource.category.index',
            'uses' => 'Resources\CategoryController@index',
        ]);
        // Add
        Route::post('create', [
            'as'   => 'resource.category.store',
            'uses' => 'Resources\CategoryController@store',
        ]);
        Route::group([
            'prefix' => '{id}',
            'where'  => ['id' => '[\d]+'],
        ], function () {
            // Update
            Route::post('', [
                'as'   => 'resource.category.update',
                'uses' => 'Resources\CategoryController@update',
            ]);
            // Delete
            Route::post('/delete', [
                'as'   => 'resource.category.destroy',
                'uses' => 'Resources\CategoryController@destroy',
            ]);
        });
    });
    // Tags
    Route::group([
        'prefix' => 'tags',
    ], function () {
        // List
        Route::get('', [
            'as'   => 'resource.tag.index',
            'uses' => 'Resources\TagController@index',
        ]);
        // Add
        Route::post('create', [
            'as'   => 'resource.tag.store',
            'uses' => 'Resources\TagController@store',
        ]);
        Route::group([
            'prefix' => '{id}',
            'where'  => ['id' => '[\d]+'],
        ], function () {
            // Update
            Route::post('', [
                'as'   => 'resource.tag.update',
                'uses' => 'Resources\TagController@update',
            ]);
            // Delete
            Route::post('/delete', [
                'as'   => 'resource.tag.destroy',
                'uses' => 'Resources\TagController@destroy',
            ]);
        });
    });
});