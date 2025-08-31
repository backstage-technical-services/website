<?php
Route::group(
    [
        'prefix' => 'members',
    ],
    function () {
        Route::get('', [
            'as' => 'member.index',
            'uses' => 'Members\MemberController@dash',
        ]);
        Route::get('dash', [
            'as' => 'member.dash',
            'uses' => 'Members\MemberController@dash',
        ]);
        Route::get('my-profile/{tab?}', [
            'as' => 'member.profile',
            'uses' => 'Members\MemberController@profile',
        ])->where([
            'tab' => 'profile|events|training',
        ]);
        Route::post('my-profile', [
            'as' => 'member.update',
            'uses' => 'Members\MemberController@update',
        ]);
        Route::get('{username}/{tab?}', [
            'as' => 'member.view',
            'uses' => 'Members\MemberController@view',
        ])->where([
            'username' => '[\w\.]+',
            'tab' => 'profile|events|training',
        ]);
    },
);
Route::get('membership', [
    'as' => 'membership.view',
    'uses' => 'Members\MemberController@membership',
]);
