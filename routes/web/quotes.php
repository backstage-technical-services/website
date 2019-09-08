<?php
// View the quotesboard
Route::get('/quotes', 'QuoteController@index')
     ->middleware('can:view,App\Models\Quote')
     ->name('quotes.view');

// Create a new quote
Route::post('/quotes', 'QuoteController@store')
     ->middleware('can:create,App\Models\Quote')
     ->name('quotes.store');

// Delete an existing quote
Route::post('/quotes/{id}/delete', 'QuoteController@delete')
     ->middleware('can:delete,App\Models\Quote')
     ->name('quotes.delete');