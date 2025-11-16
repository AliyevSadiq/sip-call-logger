<?php

namespace App\Enums;

enum CallEventType: string
{
    case CALL_STARTED = 'call_started';
    case CALL_ENDED = 'call_ended';
    case CALL_MUTED = 'call_muted';
    case CALL_UNMUTED = 'call_unmuted';
    case CALL_FORWARDED = 'call_forwarded';
}
