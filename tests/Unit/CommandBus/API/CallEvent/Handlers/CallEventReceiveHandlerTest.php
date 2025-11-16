<?php

namespace Tests\Unit\CommandBus\API\CallEvent\Handlers;

use App\CommandBus\API\CallEvent\Commands\CallEventReceiveCommand;
use App\CommandBus\API\CallEvent\Handlers\CallEventReceiveHandler;
use App\Enums\CallEventType;
use App\Jobs\CallEventJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CallEventReceiveHandlerTest extends TestCase
{
    private CallEventReceiveHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CallEventReceiveHandler();
        Queue::fake();
    }

    /** @test */
    public function it_dispatches_job_to_call_event_queue(): void
    {
        $command = $this->makeCommand();

        $this->handler->handle($command);

        Queue::assertPushedOn('call-event', CallEventJob::class);
    }

    /** @test */
    public function it_dispatches_job_exactly_once(): void
    {
        $command = $this->makeCommand();

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, 1);
    }

    /** @test */
    public function it_includes_call_id_in_job_data(): void
    {
        $command = $this->makeCommand(['call_id' => 'call_123']);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            $job->getData()['call_id'] === 'call_123'
        );
    }

    /** @test */
    public function it_includes_event_type_in_job_data(): void
    {
        $command = $this->makeCommand([
            'event_type' => CallEventType::CALL_ENDED->value
        ]);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            $job->getData()['event_type'] === CallEventType::CALL_ENDED->value
        );
    }

    /** @test */
    public function it_includes_from_number_in_payload(): void
    {
        $command = $this->makeCommand(['from' => '+994501234567']);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            $job->getData()['payload']['from'] === '+994501234567'
        );
    }

    /** @test */
    public function it_includes_to_number_in_payload(): void
    {
        $command = $this->makeCommand(['to' => '+994507654321']);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            $job->getData()['payload']['to'] === '+994507654321'
        );
    }

    /** @test */
    public function it_includes_timestamp_in_payload(): void
    {
        $command = $this->makeCommand(['timestamp' => '2025-11-16 10:30']);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            $job->getData()['payload']['timestamp'] === '2025-11-16 10:30'
        );
    }

    /** @test */
    public function it_includes_duration_when_present(): void
    {
        $command = $this->makeCommand(['duration' => 120]);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            isset($job->getData()['payload']['duration']) &&
            $job->getData()['payload']['duration'] === 120
        );
    }

    /** @test */
    public function it_excludes_duration_when_null(): void
    {
        $command = $this->makeCommand(['duration' => null]);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
        !isset($job->getData()['payload']['duration'])
        );
    }

    /** @test */
    public function it_includes_zero_duration(): void
    {
        $command = $this->makeCommand(['duration' => 0]);

        $this->handler->handle($command);

        Queue::assertPushed(CallEventJob::class, fn($job) =>
            isset($job->getData()['payload']['duration']) &&
            $job->getData()['payload']['duration'] === 0
        );
    }

    /** @test */
    public function it_handles_all_event_types(): void
    {
        $eventTypes = [
            CallEventType::CALL_STARTED,
            CallEventType::CALL_ENDED,
            CallEventType::CALL_MUTED,
            CallEventType::CALL_UNMUTED,
            CallEventType::CALL_FORWARDED,
        ];

        foreach ($eventTypes as $eventType) {
            $command = $this->makeCommand([
                'call_id' => 'call_' . $eventType->value,
                'event_type' => $eventType->value,
            ]);

            $this->handler->handle($command);
        }

        Queue::assertPushed(CallEventJob::class, count($eventTypes));
    }

    private function makeCommand(array $attributes = []): CallEventReceiveCommand
    {
        $command = new CallEventReceiveCommand();

        $defaults = [
            'call_id' => 'call_' . uniqid(),
            'from' => '+994501234567',
            'to' => '+994507654321',
            'event_type' => CallEventType::CALL_STARTED->value,
            'timestamp' => now()->format('Y-m-d H:i'),
            'duration' => null,
        ];

        $data = array_merge($defaults, $attributes);

        foreach ($data as $key => $value) {
            $command->$key = $value;
        }

        return $command;
    }
}
