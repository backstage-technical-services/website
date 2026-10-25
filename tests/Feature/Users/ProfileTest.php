<?php

namespace Tests\Feature\Users\Profile;

use App\Models\Users\User;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;
use Database\Factories\Training\CategoryFactory;
use Database\Factories\Training\Skills\ApplicationFactory;
use Database\Factories\Training\Skills\AwardedSkillFactory;
use Database\Factories\Training\Skills\SkillFactory;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Create a member who hasn't shared any of their personal details.
 */
function createPrivateMember(): User
{
    return createUser('member', [
        'email' => 'private-email@example.com',
        'phone' => '07700900123',
        'address' => '1 Private Road',
        'dob' => '2000-01-02',
        'show_email' => false,
        'show_phone' => false,
        'show_address' => false,
        'show_age' => false,
    ]);
}

/**
 * @return TestResponse<Response>
 */
function viewProfile(User $user, ?string $tab = null): TestResponse
{
    return get(route('member.view', array_filter(['username' => $user->username, 'tab' => $tab])));
}

describe('viewing a profile', function () {
    it('should require users to log in to view a profile', function () {
        $member = createPrivateMember();

        viewProfile($member)->assertRedirect(route('auth.login'));
    });

    it('should let anyone who is logged in view any profile', function (string $group) {
        $member = createUser('member', ['forename' => 'Pat', 'surname' => 'Profile']);
        loginAs($group);

        viewProfile($member)
            ->assertOk()
            ->assertSeeText(['Pat Profile', $member->username]);
    })->with(['staff', 'member', 'committee', 'super_admin']);

    it('should show archived users\' profiles', function () {
        $member = createUser('archived');
        loginAs('member');

        viewProfile($member)->assertOk();
    });

    it('should give a 404 for an unknown username', function () {
        loginAs('member');

        get(route('member.view', ['username' => 'nobody']))->assertNotFound();
    });

    it('should accept usernames with dots', function () {
        $member = createUser('member', ['username' => 'first.last']);
        loginAs('member');

        get('/members/first.last')->assertOk();
    });

    it('should show the nickname between the names', function () {
        $member = createUser('member', ['forename' => 'Pat', 'surname' => 'Profile', 'nickname' => 'Patty']);
        loginAs('member');

        viewProfile($member)->assertOk()->assertSee('<h1>Pat &quot;Patty&quot; Profile</h1>', false);
    });

    it('should show whether they are active, committee or an associate', function (string $group, array $flags) {
        $member = createUser($group);
        loginAs('member');

        $response = viewProfile($member)->assertOk();
        foreach (['Active', 'Committee', 'Associate'] as $i => $flag) {
            $icon = $flags[$i] ? 'check success' : 'remove danger';
            $response->assertSeeInOrder(["{$flag}:", "fa-lg fa fa-{$icon}"], false);
        }
    })->with([
        ['member', [true, false, false]],
        ['committee', [true, true, false]],
        ['associate', [true, false, true]],
        ['archived', [false, false, false]],
    ]);

    it('should only show the tabs for members', function (string $group, bool $tabs) {
        $user = createUser($group);
        loginAs('member');

        $response = viewProfile($user)->assertOk();
        $tabs ? $response->assertSee('id="profileTab"', false) : $response->assertDontSee('id="profileTab"', false);
    })->with([['member', true], ['committee', true], ['staff', false], ['super_admin', false]]);

    it('should give a 404 for an unknown tab', function () {
        $member = createUser('member');
        loginAs('member');

        get("/members/{$member->username}/photos")->assertNotFound();
    });

    it('should show the default picture until they upload one', function () {
        $member = createUser('member');
        loginAs('member');

        viewProfile($member)->assertOk()->assertSee('src="/images/profiles/blank.jpg"', false);
    });

    it('should show their picture once they have uploaded one', function () {
        $member = createUser('member');
        File::put(public_path("images/profiles/{$member->username}.jpg"), 'a jpeg');
        loginAs('member');

        viewProfile($member)
            ->assertOk()
            ->assertSee("src=\"/images/profiles/{$member->username}.jpg\"", false);
    });

    it('should render the tool colours', function () {
        $member = createUser('member', ['tool_colours' => 'Red and blue with initials']);
        loginAs('member');

        viewProfile($member)
            ->assertOk()
            ->assertSee('<span class="tool-colours" title="Red, Blue with initials">', false);
    });
});

describe('contact details', function () {
    it('should only show the contact details they have chosen to share', function () {
        $member = createUser('member', [
            'email' => 'shared@example.com',
            'phone' => '07700900123',
            'address' => '1 Shared Road',
            'show_email' => true,
            'show_phone' => false,
            'show_address' => true,
        ]);
        loginAs('member');

        viewProfile($member)
            ->assertOk()
            ->assertSee('shared@example.com')
            ->assertSee('1 Shared Road')
            ->assertDontSee('07700900123');
    });

    it('should say when they haven\'t shared any contact details', function () {
        $member = createPrivateMember();
        loginAs('member');

        viewProfile($member)->assertOk()->assertSee("hasn't shared any of their contact details", false);
    });

    it('should not show the edit forms or private details to other users', function (string $group) {
        $member = createPrivateMember();

        actingAs(createUser($group));
        viewProfile($member)
            ->assertOk()
            ->assertDontSee('data-type="modal-template"', false)
            ->assertDontSee('private-email@example.com')
            ->assertDontSee('07700900123')
            ->assertDontSee('1 Private Road')
            ->assertDontSee('2000-01-02');
    })->with(['staff', 'member', 'committee', 'super_admin']);

    it('should show the edit forms on your own profile', function () {
        $member = createPrivateMember();

        actingAs($member);
        viewProfile($member)->assertOk()->assertSee('data-id="contact"', false)->assertSee('07700900123');
    });

    it('should show your own profile at /members/my-profile', function (string $tab) {
        $member = createUser('member', ['forename' => 'Myself']);
        actingAs($member);

        get(route('member.profile', ['tab' => $tab]))
            ->assertOk()
            ->assertSeeText('Myself')
            ->assertSee('data-id="contact"', false);
    })->with(['profile', 'events', 'training']);

    it('should need a login to see your own profile', function () {
        get(route('member.profile'))->assertRedirect(route('auth.login'));
    });

    it('should never show the date of birth', function () {
        $member = createUser('member', ['dob' => '2000-01-02', 'show_age' => true]);
        loginAs('member');

        viewProfile($member)->assertOk()->assertDontSee('2000-01-02')->assertDontSee('02/01/2000');
    });
});

describe('the events tab', function () {
    it('should list the events they crewed or managed, newest first, with their roles', function () {
        $member = createUser('member', ['forename' => 'Casey']);
        $old = EventFactory::new()
            ->at('2026-01-01 19:00')
            ->createOne(['name' => 'Old Show']);
        CrewFactory::new()->for($old)->for($member)->role('Sound')->createOne();
        CrewFactory::new()->for($old)->for($member)->createOne();
        EventFactory::new()
            ->managedBy($member)
            ->at('2026-02-01 19:00')
            ->createOne(['name' => 'Managed Show']);
        EventFactory::new()
            ->at('2026-03-01 19:00')
            ->createOne(['name' => 'Not Theirs']);
        loginAs('member');

        viewProfile($member, 'events')
            ->assertOk()
            ->assertSeeText('Showing 1 - 2 of 2 events')
            ->assertSeeTextInOrder(['Managed Show', 'TEM', 'Old Show', 'Sound, General Crew'])
            ->assertDontSeeText('Not Theirs');
    })->group('mariadb');

    it('should say when they haven\'t got any events', function () {
        $member = createUser('member', ['forename' => 'Casey']);
        loginAs('member');

        viewProfile($member, 'events')->assertOk()->assertSee("Casey doesn't have any events yet", false);
    });

    it('should show 20 events a page', function () {
        $member = createUser('member');
        foreach (range(1, 21) as $day) {
            $event = EventFactory::new()
                ->at(sprintf('2026-01-%02d 19:00', $day))
                ->createOne(['name' => sprintf('Event %02d', $day)]);
            CrewFactory::new()->for($event)->for($member)->createOne();
        }
        loginAs('member');

        viewProfile($member, 'events')
            ->assertOk()
            ->assertSeeText('Showing 1 - 20 of 21 events')
            ->assertDontSeeText('Event 01');
        get(route('member.view', ['username' => $member->username, 'tab' => 'events', 'page' => 2]))
            ->assertOk()
            ->assertSeeText('Event 01');
    })->group('mariadb');
});

describe('the training tab', function () {
    it('should list the skills in each category, with their levels and pending applications', function () {
        $member = createUser('member');
        $category = CategoryFactory::new()->createOne(['name' => 'Lighting']);
        $rigging = SkillFactory::new()
            ->for($category)
            ->createOne(['name' => 'Rigging']);
        $focusing = SkillFactory::new()
            ->for($category)
            ->createOne(['name' => 'Focusing']);
        SkillFactory::new()->createOne(['name' => 'Ladders']);
        AwardedSkillFactory::new()->for($rigging)->for($member)->level(2)->createOne();
        ApplicationFactory::new()->for($focusing)->for($member)->createOne();
        loginAs('member');

        viewProfile($member, 'training')
            ->assertOk()
            ->assertSeeInOrder(['Lighting', '1 / 2', 'Uncategorised', '0 / 1'], false)
            ->assertSeeInOrder(['Focusing', 'Application pending', 'Rigging', 'title="Level 2"'], false)
            ->assertSeeText('Ladders');
    });
});
