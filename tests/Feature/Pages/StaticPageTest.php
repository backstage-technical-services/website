<?php

namespace Tests\Feature\Pages\StaticPage;

use function Pest\Laravel\get;

/*
 * The static pages are Blade templates in the StaticPage module, so they need a code change to edit. The module's own
 * tests check their content.
 */

dataset('pages', [
    'home' => ['/', 'Backstage Technical Services'],
    'about' => ['/page/about', 'About Us'],
    'FAQ' => ['/page/faq', 'Frequently Asked Questions'],
    'links' => ['/page/links', 'Links'],
    'privacy policy' => ['/page/privacy-policy', 'Privacy Policy'],
    'booking terms' => ['/contact/book/terms', 'Terms and Conditions for the Provision of Services'],
]);

it('should show each page to everyone', function (string $url, string $text, string $group) {
    loginAs($group);

    get($url)->assertOk()->assertSeeText($text);
})
    ->with('pages')
    ->with(['guest', 'staff', 'member', 'super_admin']);

it('should give a 404 for any other page', function () {
    get('/page/contact')->assertNotFound();
});
