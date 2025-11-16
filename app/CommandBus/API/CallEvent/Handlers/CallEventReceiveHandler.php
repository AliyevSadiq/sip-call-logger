<?php


namespace App\CommandBus\API\CallEvent\Handlers;


use App\CommandBus\API\CallEvent\Commands\CallEventReceiveCommand;
use App\Jobs\CallEventJob;

class CallEventReceiveHandler
{
    public function handle(CallEventReceiveCommand $command)
    {
        $payload = $this->preparePayload($command);

        CallEventJob::dispatch([
            'call_id' => $command->call_id,
            'event_type' => $command->event_type,
            'payload' => $payload,
        ])->onQueue('call-event');
    }


    private function preparePayload(CallEventReceiveCommand $command): array
    {
        $payload = [
            'from' => $command->from,
            'to' => $command->to,
            'timestamp' => $command->timestamp,
        ];

        if (!is_null($command->duration)) {
            $payload['duration'] = $command->duration;
        }
        return $payload;
    }
}
