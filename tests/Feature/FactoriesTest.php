<?php

namespace Tests\Feature;

use App\Models\Events\Event;
use App\Models\Users\Group;
use App\Models\Users\User;
use Database\Factories\Awards\AwardFactory;
use Database\Factories\Awards\NominationFactory as AwardNominationFactory;
use Database\Factories\Awards\SeasonFactory;
use Database\Factories\Awards\VoteFactory;
use Database\Factories\Committee\RoleFactory;
use Database\Factories\Elections\ElectionFactory;
use Database\Factories\Elections\NominationFactory as ElectionNominationFactory;
use Database\Factories\Equipment\BreakageFactory;
use Database\Factories\Equipment\BreakageImageFactory;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EmailFactory;
use Database\Factories\Events\EventFactory;
use Database\Factories\Events\TimeFactory;
use Database\Factories\LogFactory;
use Database\Factories\QuoteFactory;
use Database\Factories\Resources\CategoryFactory as ResourceCategoryFactory;
use Database\Factories\Resources\IssueFactory;
use Database\Factories\Resources\ResourceFactory;
use Database\Factories\Resources\TagFactory;
use Database\Factories\Training\CategoryFactory as TrainingCategoryFactory;
use Database\Factories\Training\Skills\ApplicationFactory;
use Database\Factories\Training\Skills\AwardedSkillFactory;
use Database\Factories\Training\Skills\SkillFactory;
use Database\Factories\Users\GroupFactory;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\travelTo;

it('should create a valid row', function (Factory $factory) {
    assertModelExists($factory->createOne());
})->with([
    'award' => fn() => AwardFactory::new(),
    'award nomination' => fn() => AwardNominationFactory::new(),
    'award season' => fn() => SeasonFactory::new(),
    'award vote' => fn() => VoteFactory::new(),
    'breakage' => fn() => BreakageFactory::new(),
    'breakage image' => fn() => BreakageImageFactory::new(),
    'committee role' => fn() => RoleFactory::new(),
    'election' => fn() => ElectionFactory::new(),
    'election nomination' => fn() => ElectionNominationFactory::new(),
    'event' => fn() => EventFactory::new(),
    'event crew' => fn() => CrewFactory::new(),
    'event email' => fn() => EmailFactory::new(),
    'event time' => fn() => TimeFactory::new(),
    'log' => fn() => LogFactory::new(),
    'quote' => fn() => QuoteFactory::new(),
    'resource' => fn() => ResourceFactory::new(),
    'resource category' => fn() => ResourceCategoryFactory::new(),
    'resource issue' => fn() => IssueFactory::new(),
    'resource tag' => fn() => TagFactory::new(),
    'skill' => fn() => SkillFactory::new(),
    'skill application' => fn() => ApplicationFactory::new(),
    'awarded skill' => fn() => AwardedSkillFactory::new(),
    'training category' => fn() => TrainingCategoryFactory::new(),
    'user' => fn() => UserFactory::new(),
    'user group' => fn() => GroupFactory::new()->named('staff'),
]);

it('should seed the five user groups', function () {
    foreach (User::$AccountTypes as $name => $title) {
        assertDatabaseHas('user_groups', ['name' => $name, 'title' => $title]);
    }
});

it('should create users in each group', function (string $group, string $groupName, bool $status) {
    $user = createUser($group);

    expect(Group::find($user->user_group_id)?->name)->toBe($groupName);
    expect($user->status)->toBe($status);
})->with([
    ['member', 'member', true],
    ['associate', 'associate', true],
    ['committee', 'committee', true],
    ['staff', 'staff', true],
    ['super_admin', 'super_admin', true],
    ['archived', 'member', false],
]);

it('should give events a time slot', function () {
    $event = EventFactory::new()->createOne();

    expect($event->times)->toHaveCount(1);
});

it('should give events the chosen time slot', function () {
    $event = EventFactory::new()->at('2026-11-01 19:30', '2026-11-01 23:00')->createOne();

    assertDatabaseHas('event_times', [
        'event_id' => $event->id,
        'start' => '2026-11-01 19:30:00',
        'end' => '2026-11-01 23:00:00',
    ]);
    expect($event->times)->toHaveCount(1);
});

it('should create events without time slots', function () {
    $event = EventFactory::new()->withoutTimes()->createOne();

    expect($event->times)->toBeEmpty();
});

it('should store the client and venue types as 0 for anything but an event', function () {
    $social = EventFactory::new()->social()->createOne();

    expect($social->type)->toBe(Event::TYPE_SOCIAL);
    expect($social->client_type)->toBe(0);
    expect($social->venue_type)->toBe(0);
});

it('should store time slots without shifting them, even in the summer', function () {
    travelTo(Carbon::parse('2026-07-01 12:00:00'));

    $time = TimeFactory::new()->createOne(['start' => '2026-07-02 19:00:00', 'end' => '2026-07-02 23:00:00']);

    assertDatabaseHas('event_times', ['id' => $time->id, 'start' => '2026-07-02 19:00:00']);
});

it('should write resource files to the temporary uploads directory', function () {
    $resource = ResourceFactory::new()->withIssue()->createOne();

    expect($resource->getFullPath())
        ->toBe(uploadPath("resources/resources/{$resource->id}/iss01.pdf"))
        ->toBeFile();
});
