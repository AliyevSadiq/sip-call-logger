<?php

namespace Database\Factories;

use App\Enums\CallEventType;
use App\Models\CallEvent;
use Illuminate\Database\Eloquent\Factories\Factory;


class CallEventFactory extends Factory
{
    protected $model = CallEvent::class;

    public function definition(): array
    {
        return [
            'call_id' => 'call_' . $this->faker->unique()->uuid(),
            'event_type' => $this->faker->randomElement(CallEventType::cases())->value,
            'payload' => [
                'from' => $this->faker->e164PhoneNumber(),
                'to' => $this->faker->e164PhoneNumber(),
                'timestamp' => now()->format('Y-m-d H:i'),
            ],
        ];
    }

    public function callStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => CallEventType::CALL_STARTED->value,
        ]);
    }

    public function callEnded(int $duration = 120): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => CallEventType::CALL_ENDED->value,
            'payload' => array_merge($attributes['payload'] ?? [], [
                'duration' => $duration,
            ]),
        ]);
    }

    public function callMuted(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => CallEventType::CALL_MUTED->value,
        ]);
    }

    public function callUnmuted(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => CallEventType::CALL_UNMUTED->value,
        ]);
    }

    public function callForwarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => CallEventType::CALL_FORWARDED->value,
        ]);
    }

    public function withCallId(string $callId): static
    {
        return $this->state(fn (array $attributes) => [
            'call_id' => $callId,
        ]);
    }

    public function withDuration(int $duration): static
    {
        return $this->state(fn (array $attributes) => [
            'payload' => array_merge($attributes['payload'] ?? [], [
                'duration' => $duration,
            ]),
        ]);
    }
}
