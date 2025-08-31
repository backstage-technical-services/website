<?php
Route::get('login', [
    'as' => 'auth.login',
    'uses' => 'Auth\LoginController@showLoginForm',
]);
Route::post('login', [
    'as' => 'auth.login.do',
    'uses' => 'Auth\LoginController@login',
]);
Route::get('logout', [
    'as' => 'auth.logout',
    'uses' => 'Auth\LoginController@logout',
]);
Route::group(
    [
        'prefix' => 'password',
    ],
    function () {
        Route::get('email', [
            'as' => 'auth.pwd.email',
            'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm',
        ]);
        Route::post('email', [
            'as' => 'auth.pwd.email.do',
            'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail',
        ]);
        Route::get('reset/{token}', [
            'as' => 'auth.pwd.reset',
            'uses' => 'Auth\ResetPasswordController@showResetForm',
        ]);
        Route::post('reset/{token}', [
            'as' => 'auth.pwd.reset.do',
            'uses' => 'Auth\ResetPasswordController@reset',
        ]);
    },
);
