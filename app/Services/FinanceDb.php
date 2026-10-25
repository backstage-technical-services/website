<?php

namespace App\Services;

/**
 * Sends data to the BTS finance database. This is its own class so that the tests can swap it for a fake.
 */
class FinanceDb
{
    /**
     * Add an event to the finance database. This uses cURL directly, so the response is echoed into the page and
     * there's no timeout.
     *
     * @param array<string, mixed> $fields
     */
    public function addEvent(array $fields): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('bts.finance_db.url'));
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_exec($ch);
        curl_close($ch);
    }
}
