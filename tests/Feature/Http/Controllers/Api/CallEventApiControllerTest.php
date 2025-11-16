<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\CallEventType;
use App\Jobs\CallEventJob;
use App\Models\CallEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CallEventApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/call-event';

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload());

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_accepts_valid_call_started_event(): void
    {
        $payload = $this->validPayload([
            'event_type' => CallEventType::CALL_STARTED->value,
        ]);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertOk()
            ->assertJson(['status' => 'queued']);

        Queue::assertPushedOn('call-event', CallEventJob::class);
    }

    /** @test */
    public function it_accepts_valid_call_ended_event_with_duration(): void
    {
        $payload = $this->validPayload([
            'event_type' => CallEventType::CALL_ENDED->value,
            'duration' => 120,
        ]);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertOk()
            ->assertJson(['status' => 'queued']);
    }

    /** @test */
    public function it_accepts_zero_duration_for_call_ended_event(): void
    {
        $payload = $this->validPayload([
            'event_type' => CallEventType::CALL_ENDED->value,
            'duration' => 0,
        ]);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertOk();
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $response = $this->actingAsUser()->postJson($this->endpoint, []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'call_id',
                'from',
                'to',
                'event_type',
                'timestamp',
            ]);
    }

    /** @test */
    public function it_requires_duration_for_call_ended_event(): void
    {
        $payload = $this->validPayload([
            'event_type' => CallEventType::CALL_ENDED->value,
        ]);
        unset($payload['duration']);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['duration']);
    }

    /** @test */
    public function it_rejects_negative_duration(): void
    {
        $payload = $this->validPayload([
            'event_type' => CallEventType::CALL_ENDED->value,
            'duration' => -10,
        ]);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['duration']);
    }

    /** @test */
    public function it_validates_event_type_enum(): void
    {
        $payload = $this->validPayload([
            'event_type' => 'invalid_event_type',
        ]);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);
    }

    /** @test */
    public function it_validates_timestamp_format(): void
    {
        $payload = $this->validPayload([
            'timestamp' => 'invalid-timestamp',
        ]);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['timestamp']);
    }

    /** @test */
    public function it_requires_unique_call_id(): void
    {
        CallEvent::factory()->create(['call_id' => 'duplicate_call_id']);

        $payload = $this->validPayload(['call_id' => 'duplicate_call_id']);

        $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['call_id']);
    }

    /** @test */
    public function it_accepts_all_valid_event_types(): void
    {
        $eventTypes = [
            CallEventType::CALL_STARTED,
            CallEventType::CALL_MUTED,
            CallEventType::CALL_UNMUTED,
            CallEventType::CALL_FORWARDED,
        ];

        foreach ($eventTypes as $eventType) {
            $payload = $this->validPayload([
                'call_id' => 'call_' . $eventType->value . '_' . uniqid(),
                'event_type' => $eventType->value,
            ]);

            $response = $this->actingAsUser()->postJson($this->endpoint, $payload);

            $response->assertOk();
        }
    }

    /** @test */
    public function it_dispatches_job_with_correct_data(): void
    {
        $payload = $this->validPayload([
            'call_id' => 'test_call_123',
            'from' => '+994501234567',
            'to' => '+994507654321',
            'event_type' => CallEventType::CALL_STARTED->value,
        ]);

        $this->actingAsUser()->postJson($this->endpoint, $payload);

        Queue::assertPushed(CallEventJob::class, function ($job) use ($payload) {
            return $job->getData()['call_id'] === $payload['call_id'] &&
                $job->getData()['event_type'] === $payload['event_type'] &&
                $job->getData()['payload']['from'] === $payload['from'] &&
                $job->getData()['payload']['to'] === $payload['to'];
        });
    }

    private function actingAsUser(): self
    {
        return $this->actingAs(User::factory()->create());
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'call_id' => 'call_' . uniqid(),
            'from' => '+994501234567',
            'to' => '+994507654321',
            'event_type' => CallEventType::CALL_STARTED->value,
            'timestamp' => now()->format('Y-m-d H:i'),
        ], $overrides);
    }
}
