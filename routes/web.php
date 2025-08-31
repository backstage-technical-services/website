<?php

/*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */

Route::get('/', [
    'as' => 'home',
    'uses' => function () {
        return view('home');
    },
]);

include base_path('routes/web/auth.php');
include base_path('routes/web/awards.php');
include base_path('routes/web/backups.php');
include base_path('routes/web/committee.php');
include base_path('routes/web/contact.php');
include base_path('routes/web/election.php');
include base_path('routes/web/equipment.php');
include base_path('routes/web/events.php');
include base_path('routes/web/logs.php');
include base_path('routes/web/media.php');
include base_path('routes/web/members.php');
include base_path('routes/web/page.php');
include base_path('routes/web/quotes.php');
include base_path('routes/web/resources.php');
include base_path('routes/web/training.php');
include base_path('routes/web/users.php');
