<?php

namespace App\CommandBus\API\CallEvent\Commands;

use App\CommandBus\Core\Command;

class CallEventReceiveCommand extends Command
{
    public string $call_id;
    public string $from;
    public string $to;
    public string $event_type;
    public string $timestamp;
    public ?int $duration=null;

}
