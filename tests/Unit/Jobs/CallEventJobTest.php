<?php

namespace Tests\Unit\Jobs;

use App\Enums\CallEventType;
use App\Jobs\CallEventJob;
use App\Models\CallEvent;
use App\Repository\ICallEventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CallEventJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calls_repository_create_method(): void
    {
        $data = $this->validData();

        $repository = $this->mockRepository();
        $repository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn(new CallEvent($data));

        $job = new CallEventJob($data);
        $job->handle($repository);
    }

    /** @test */
    public function it_passes_correct_data_to_repository(): void
    {
        $data = $this->validData([
            'call_id' => 'specific_call_id',
            'event_type' => CallEventType::CALL_MUTED->value,
        ]);

        $repository = $this->mockRepository();
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function ($arg) use ($data) {
                return $arg['call_id'] === $data['call_id'] &&
                    $arg['event_type'] === $data['event_type'];
            })
            ->andReturn(new CallEvent($data));

        $job = new CallEventJob($data);
        $job->handle($repository);
    }

    /** @test */
    public function it_stores_payload_as_array(): void
    {
        $data = $this->validData([
            'payload' => [
                'from' => '+994501234567',
                'to' => '+994507654321',
                'timestamp' => '2025-11-16 10:30',
            ],
        ]);

        $repository = $this->mockRepository();
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function ($arg) {
                return is_array($arg['payload']);
            })
            ->andReturn(new CallEvent($data));

        $job = new CallEventJob($data);
        $job->handle($repository);
    }

    /** @test */
    public function it_creates_database_record_with_call_started_event(): void
    {
        $data = $this->validData([
            'call_id' => 'call_started_001',
            'event_type' => CallEventType::CALL_STARTED->value,
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $this->assertDatabaseHas('call_events', [
            'call_id' => 'call_started_001',
            'event_type' => CallEventType::CALL_STARTED->value,
        ]);
    }

    /** @test */
    public function it_creates_database_record_with_call_ended_event(): void
    {
        $data = $this->validData([
            'call_id' => 'call_ended_001',
            'event_type' => CallEventType::CALL_ENDED->value,
            'payload' => [
                'from' => '+994501234567',
                'to' => '+994507654321',
                'timestamp' => '2025-11-16 10:35',
                'duration' => 120,
            ],
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $callEvent = CallEvent::where('call_id', 'call_ended_001')->first();

        $this->assertNotNull($callEvent);
        $this->assertEquals(CallEventType::CALL_ENDED->value, $callEvent->event_type);
        $this->assertEquals(120, $callEvent->payload['duration']);
    }

    /** @test */
    public function it_creates_database_record_with_call_muted_event(): void
    {
        $data = $this->validData([
            'call_id' => 'call_muted_001',
            'event_type' => CallEventType::CALL_MUTED->value,
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $this->assertDatabaseHas('call_events', [
            'call_id' => 'call_muted_001',
            'event_type' => CallEventType::CALL_MUTED->value,
        ]);
    }

    /** @test */
    public function it_creates_database_record_with_call_unmuted_event(): void
    {
        $data = $this->validData([
            'call_id' => 'call_unmuted_001',
            'event_type' => CallEventType::CALL_UNMUTED->value,
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $this->assertDatabaseHas('call_events', [
            'call_id' => 'call_unmuted_001',
            'event_type' => CallEventType::CALL_UNMUTED->value,
        ]);
    }

    /** @test */
    public function it_creates_database_record_with_call_forwarded_event(): void
    {
        $data = $this->validData([
            'call_id' => 'call_forwarded_001',
            'event_type' => CallEventType::CALL_FORWARDED->value,
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $this->assertDatabaseHas('call_events', [
            'call_id' => 'call_forwarded_001',
            'event_type' => CallEventType::CALL_FORWARDED->value,
        ]);
    }

    /** @test */
    public function it_stores_zero_duration_correctly(): void
    {
        $data = $this->validData([
            'call_id' => 'call_zero_duration',
            'event_type' => CallEventType::CALL_ENDED->value,
            'payload' => [
                'from' => '+994501234567',
                'to' => '+994507654321',
                'timestamp' => '2025-11-16 10:40',
                'duration' => 0,
            ],
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $callEvent = CallEvent::where('call_id', 'call_zero_duration')->first();

        $this->assertNotNull($callEvent);
        $this->assertEquals(0, $callEvent->payload['duration']);
    }

    /** @test */
    public function it_stores_payload_with_all_required_fields(): void
    {
        $data = $this->validData([
            'call_id' => 'call_full_payload',
            'payload' => [
                'from' => '+994501234567',
                'to' => '+994507654321',
                'timestamp' => '2025-11-16 10:45',
            ],
        ]);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $callEvent = CallEvent::where('call_id', 'call_full_payload')->first();

        $this->assertNotNull($callEvent);
        $this->assertArrayHasKey('from', $callEvent->payload);
        $this->assertArrayHasKey('to', $callEvent->payload);
        $this->assertArrayHasKey('timestamp', $callEvent->payload);
    }

    /** @test */
    public function it_creates_record_with_timestamp(): void
    {
        $data = $this->validData(['call_id' => 'call_with_timestamp']);

        $job = new CallEventJob($data);
        $job->handle(app(ICallEventRepository::class));

        $callEvent = CallEvent::where('call_id', 'call_with_timestamp')->first();

        $this->assertNotNull($callEvent);
        $this->assertNotNull($callEvent->created_at);
        $this->assertNotNull($callEvent->updated_at);
    }

    private function mockRepository(): MockInterface
    {
        return Mockery::mock(ICallEventRepository::class);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'call_id' => 'call_' . uniqid(),
            'event_type' => CallEventType::CALL_STARTED->value,
            'payload' => [
                'from' => '+994501234567',
                'to' => '+994507654321',
                'timestamp' => now()->format('Y-m-d H:i'),
            ],
        ], $overrides);
    }
}
