<?php

namespace Tests\Feature\Users\Menu;

use function Pest\Laravel\get;

/*
 * The main menu, which every page shows. What's in it depends on the user's group.
 */

/**
 * @return list<string>
 */
function menuLinks(): array
{
    $html = (string) get(route('home'))->assertOk()->getContent();
    preg_match('/<ul class="nav navbar-nav dropdown-menu">(.*?)<\/ul>\s*<\/div>/s', $html, $menu);
    preg_match_all('/<a href="[^"]*"[^>]*>(.*?)<\/a>/s', $menu[1] ?? '', $links);

    return array_map(fn(string $text) => trim(html_entity_decode(strip_tags($text), ENT_QUOTES)), $links[1]);
}

dataset('everyone', [
    'Home',
    'About Us',
    'Media',
    'Image Gallery',
    'Videos',
    'The Committee',
    'Events Diary',
    "Members' Area",
    'Resources',
    'Links',
    'FAQ',
    'Enquiries & Book Us',
]);

it('should show the public links to everyone', function (string $link) {
    expect(menuLinks())->toContain($link);
})->with('everyone');

it('should show guests the public links only', function () {
    expect(menuLinks())->toBe([
        'Home',
        'About Us',
        'Media',
        'Image Gallery',
        'Videos',
        'The Committee',
        'Events Diary',
        "Members' Area",
        'Resources',
        'Links',
        'FAQ',
        'Enquiries & Book Us',
    ]);
});

it('should show the member links to members', function (string $group) {
    loginAs($group);

    expect(menuLinks())->toContain(
        'Submit event report',
        'My Profile',
        'My events',
        'My training',
        'The Membership',
        'Quotes Board',
        'Wiki',
        'Network Drive',
        'Asset register',
        'View repairs db',
        'Report broken kit',
        'PAT database',
        'Skills matrix',
        'View skills',
        'Committee elections',
        'Awards',
        'PC deployment portal',
        'Network management portal',
        'Telephony portal',
        'Report a Near Miss',
        'Report an Accident',
        'Event Reports',
        'Event Risk Assessments',
        'Meeting Minutes',
        'Meeting Agendas',
        'Log out',
    );
})->with(['member', 'associate', 'committee']);

it('should only show the admin links to the committee', function (string $group, bool $shown) {
    loginAs($group);

    $adminLinks = [
        'View all events',
        'Create a new event',
        'View all users',
        'Create a new user',
        'View categories',
        'Review applications',
        'Skills log',
        'Backups',
    ];
    $shown ? expect(menuLinks())->toContain(...$adminLinks) : expect(menuLinks())->not->toContain(...$adminLinks);
})->with([['member', false], ['associate', false], ['committee', true]]);

it('should only show super admins the event admin links, as they aren\'t members', function () {
    loginAs('super_admin');

    $links = menuLinks();
    expect($links)->toContain('View all events', 'Create a new event', 'My Profile', 'Event Reports', 'Log out');
    expect($links)->not->toContain(
        'View all users',
        'Backups',
        'The Membership',
        'Submit event report',
        'Quotes Board',
    );
});

it('should show staff their profile, the resource categories and logging out', function () {
    loginAs('staff');

    $links = menuLinks();
    expect($links)->toContain('My Profile', 'Event Reports', 'Log out');
    expect($links)->not->toContain('The Membership', 'Submit event report', 'View all events', 'Quotes Board');
});
