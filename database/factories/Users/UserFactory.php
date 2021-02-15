<?php

namespace Database\Factories\Users;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    const DIARY_PREFERENCES_ALL = '{"event_types":["event","training","social","meeting","hidden"],"crewing":"*"}';
    
    protected string $model = User::class;
    
    public function definition()
    {
        return [
            'username'          => $this->faker->userName,
            'email'             => $this->faker->email,
            'password'          => bcrypt('password'),
            'forename'          => $this->faker->firstName,
            'surname'           => $this->faker->lastName,
            'nickname'          => null,
            'phone'             => null,
            'address'           => null,
            'tool_colours'      => null,
            'dob'               => null,
            'show_email'        => false,
            'show_phone'        => false,
            'show_address'      => false,
            'show_age'          => false,
            'diary_preferences' => self::DIARY_PREFERENCES_ALL,
            'export_token'      => null,
            'remember_token'    => null,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
            'status'            => true
        ];
    }
}
