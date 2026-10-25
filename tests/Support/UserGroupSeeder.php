<?php

namespace Tests\Support;

use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the five user groups, which in production exist only in the database. The tests run this once, after
 * migrating. The IDs here may not match production's, so tests shouldn't rely on them.
 */
class UserGroupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::$AccountTypes as $name => $title) {
            DB::table('user_groups')->insert(['name' => $name, 'title' => $title]);
        }
    }
}
