<?php

namespace App\Repository;

use App\Models\CallEvent;

class CallEventRepository implements ICallEventRepository
{

    public function create(array $data): CallEvent
    {
        return CallEvent::create($data);
    }
}
