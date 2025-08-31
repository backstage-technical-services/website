<?php

use Illuminate\Support\Facades\Route;
use Modules\StaticPage\Http\Controllers\StaticPageController;

Route::get('/', [StaticPageController::class, 'landing'])->name('home');
Route::get('/page/about', [StaticPageController::class, 'about'])->name('page.about');
Route::get('/page/faq', [StaticPageController::class, 'faq'])->name('page.faq');
Route::get('/page/links', [StaticPageController::class, 'links'])->name('page.links');
Route::get('/page/privacy-policy', [StaticPageController::class, 'privacyPolicy'])->name('page.privacy-policy');
