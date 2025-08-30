<?php

use function Pest\Laravel\get;

it('should show the homepage', function () {
    get('/')
       ->assertSuccessful()
       ->assertSeeText('Backstage Technical Services is a society formed of students at the University of Bath');
});

it('should show the about page', function () {
    get('/page/about')
        ->assertSuccessful()
        ->assertSeeText('About Us');
})->skip('Needs to be made a static page');

it('should show the faq page', function () {
    get('/page/faq')
        ->assertSuccessful()
        ->assertSeeText('Frequently Asked Questions');
})->skip('Needs to be made a static page');

it('should show the links page', function () {
    get('/page/links')
        ->assertSuccessful()
        ->assertSeeText('Links');
})->skip('Needs to be made a static page');

it('should show the privacy policy page', function () {
    get('/page/privacy-policy')
        ->assertSuccessful()
        ->assertSeeText('Privacy Policy');
})->skip('Needs to be made a static page');

it('should show the terms and conditions page', function () {
    get('/contact/book/terms')
        ->assertSuccessful()
        ->assertSeeText('Terms and Conditions');
});
