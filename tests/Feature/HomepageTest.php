<?php

use function Pest\Laravel\get;

it('should show the homepage', function () {
    get('/')
       ->assertSuccessful()
       ->assertSeeText('Backstage Technical Services is a society formed of students at the University of Bath');
});
