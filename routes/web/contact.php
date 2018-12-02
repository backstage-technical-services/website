<?php
Route::group([
    'prefix' => 'contact',
], function () {
    // Enquiries
    Route::get('enquiries', [
        'as'   => 'contact.enquiries',
        'uses' => 'Contact\EnquiryController@showForm',
    ]);
    Route::post('enquiries', [
        'as'   => 'contact.enquiries.do',
        'uses' => 'Contact\EnquiryController@process',
    ]);
    // Book
    Route::get('book', [
        'as'   => 'contact.book.alt',
        'uses' => 'Contact\BookingController@showForm',
    ]);
    Route::post('book', [
        'as'   => 'contact.book.alt.do',
        'uses' => 'Contact\BookingController@process',
    ]);
    // Book T&Cs
    Route::get('book/terms', [
        'as'   => 'contact.book.terms',
        'uses' => 'Contact\BookingController@getTerms',
    ]);
    // Feedback
    Route::get('feedback', [
        'as'   => 'contact.feedback',
        'uses' => 'Contact\FeedbackController@showForm',
    ]);
    Route::post('feedback', [
        'as'   => 'contact.feedback.do',
        'uses' => 'Contact\FeedbackController@process',
    ]);
    // Report accident
    Route::get('accident', [
        'as'   => 'contact.accident',
        'uses' => 'Contact\AccidentController@showForm',
    ]);
    Route::post('accident', [
        'as'   => 'contact.accident.do',
        'uses' => 'Contact\AccidentController@process',
    ]);
    // Report near miss
    Route::get('near-miss', [
        'as'   => 'contact.near-miss',
        'uses' => 'Contact\NearMissController@showForm',
    ]);
    Route::post('near-miss', [
        'as'   => 'contact.near-miss.send',
        'uses' => 'Contact\NearMissController@process',
    ]);
});
// Book
Route::get('book-us', [
    'as'   => 'contact.book',
    'uses' => 'Contact\BookingController@showForm',
]);
Route::post('book-us', [
    'as'   => 'contact.book.do',
    'uses' => 'Contact\BookingController@process',
]);