<?php

test('basic test', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
