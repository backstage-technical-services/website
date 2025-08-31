<?php
Route::get('logs', [
    'as' => 'logs.index',
    'uses' => 'LogController@index',
]);
