<?php

use function Pest\Laravel\get;
uses(Tests\TestCase::class);

it('should show the homepage', function () {
    get('/')
        ->assertSuccessful()
        ->assertSeeText('Backstage Technical Services is a society formed of students at the University of Bath');
});

it('should show the about page', function () {
    get('/page/about')
        ->assertSuccessful()
        ->assertSeeHtml('<h1 class="page-header">About Us</h1>')
        ->assertSeeText('What is Backstage?');
});

it('should show the faq page', function () {
    get('/page/faq')
        ->assertSuccessful()
        ->assertSeeHtml('<h1 class="page-header">Frequently Asked Questions</h1>')
        ->assertSeeText('How can I book Backstage?');
});

it('should show the links page', function () {
    get('/page/links')
        ->assertSuccessful()
        ->assertSeeHtml('<h1 class="page-header">Links</h1>')
        ->assertSeeText('Other Useful Links');
});

it('should show the privacy policy page', function () {
    get('/page/privacy-policy')
        ->assertSuccessful()
        ->assertSeeHtml('<h1 class="page-header">Privacy Policy</h1>')
        ->assertSeeText('What is this Privacy Policy for?');
});

it('should show the terms and conditions page', function () {
    get('/contact/book/terms')
        ->assertSuccessful()
        ->assertSeeHtml('<h2 class="page-header">Terms and Conditions for the Provision of Services</h2>')
        ->assertSeeText('Please read these terms and conditions carefully.');
});
