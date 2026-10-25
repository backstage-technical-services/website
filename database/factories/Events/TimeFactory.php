<?php

namespace Database\Factories\Events;

use App\Models\Events\Time;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Time slots are shifted by the browser's timezone offset when they're saved (see AccountsForTimezones). The tests
 * use an offset of 0, so the stored times are the ones given here.
 *
 * @extends Factory<Time>
 */
class TimeFactory extends Factory
{
    protected $model = Time::class;

    public function definition(): array
    {
        $start = now()->addDay()->setTime(19, 0);

        return [
            'event_id' => EventFactory::new()->withoutTimes(),
            'name' => 'Show',
            'start' => $start,
            'end' => $start->copy()->addHours(4),
        ];
    }
}
