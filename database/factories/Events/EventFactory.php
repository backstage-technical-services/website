<?php

namespace Database\Factories\Events;

use App\Models\Events\Event;
use App\Models\Users\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Events get their dates from their time slots, so by default this gives each event one slot, from 19:00 to 23:00
 * tomorrow. Use at() to choose the slot, or withoutTimes() for none. Like EventController@store, new events have none
 * of their paperwork done.
 *
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->word() . ' ' . fake()->word()),
            'venue' => fake()->randomElement(['Plug', 'Score', 'Founders Hall', 'Arts Theatre']),
            'em_id' => null,
            'description' => fake()->paragraph(),
            'type' => Event::TYPE_EVENT,
            'crew_list_status' => Event::CREW_LIST_OPEN,
            'client_type' => 1,
            'venue_type' => 1,
            'production_charge' => null,
            'paperwork' => [
                'risk_assessment' => false,
                'insurance' => false,
                'finance_em' => false,
                'finance_treas' => false,
                'event_report' => false,
            ],
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            if (!$event->times()->exists()) {
                TimeFactory::new()
                    ->for($event)
                    ->create(['name' => $event->name]);
            }
        });
    }

    /**
     * Give the event a single time slot.
     */
    public function at(DateTimeInterface|string $start, DateTimeInterface|string|null $end = null): static
    {
        $start = Carbon::parse($start);

        return $this->has(
            TimeFactory::new()->state(
                fn(array $attributes, ?Model $event) => [
                    'name' => $event?->getAttribute('name') ?? 'Show',
                    'start' => $start,
                    'end' => $end === null ? $start->copy()->addHours(4) : Carbon::parse($end),
                ],
            ),
            'times',
        );
    }

    /**
     * Give the event a time slot that finished yesterday.
     */
    public function past(): static
    {
        return $this->at(now()->subDay()->setTime(19, 0));
    }

    /**
     * Create the event without any time slots. Many pages fail for these.
     */
    public function withoutTimes(): static
    {
        return $this->afterCreating(fn(Event $event) => $event->times()->delete());
    }

    public function managedBy(User $user): static
    {
        return $this->state(['em_id' => $user->id]);
    }

    /**
     * Set the type. Like EventController@store, only events get a client and venue type: the other types store '',
     * which MariaDB turns into 0.
     */
    public function ofType(int $type): static
    {
        return $this->state(
            fn() => $type === Event::TYPE_EVENT
                ? ['type' => $type]
                : ['type' => $type, 'client_type' => 0, 'venue_type' => 0],
        );
    }

    public function training(): static
    {
        return $this->ofType(Event::TYPE_TRAINING);
    }

    public function social(): static
    {
        return $this->ofType(Event::TYPE_SOCIAL);
    }

    public function meeting(): static
    {
        return $this->ofType(Event::TYPE_MEETING);
    }

    public function hidden(): static
    {
        return $this->ofType(Event::TYPE_HIDDEN);
    }

    public function crewList(int $status): static
    {
        return $this->state(['crew_list_status' => $status]);
    }
}
