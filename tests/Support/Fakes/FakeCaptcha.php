<?php

namespace Tests\Support\Fakes;

use Anhskohbo\NoCaptcha\NoCaptcha;

/**
 * The reCAPTCHA client with the call to Google replaced. Google accepts only FakeCaptcha::PASS; anything else fails.
 */
class FakeCaptcha extends NoCaptcha
{
    /**
     * The captcha response that passes.
     */
    public const PASS = 'fake-captcha-pass';

    /**
     * The requests sent to "Google", in order.
     *
     * @var list<array<string, mixed>>
     */
    public array $verifications = [];

    public function __construct()
    {
        parent::__construct('fake-secret', 'fake-sitekey');
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array{success: bool}
     */
    protected function sendRequestVerify(array $query = [])
    {
        $this->verifications[] = $query;

        return ['success' => ($query['response'] ?? null) === self::PASS];
    }
}
