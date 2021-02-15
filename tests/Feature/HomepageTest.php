<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    const HOME_TEXT = "Backstage Technical Services is a society formed of students at the University of Bath";

    public function test_theHomepageShouldBeRendered()
    {
        $response = $this->get('/');

        $response->assertSuccessful()
            ->assertSeeText(self::HOME_TEXT);
    }
}
