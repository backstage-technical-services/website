<?php

namespace Database\Factories;

use App\Models\Log;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An entry in the audit log. The model observers write these whenever a model is created, updated or deleted.
 *
 * @extends Factory<Log>
 */
class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'action' => 'auth.login',
            'payload' => null,
            'status' => true,
        ];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public function payload(array $payload): static
    {
        return $this->state(['payload' => json_encode($payload)]);
    }
}
