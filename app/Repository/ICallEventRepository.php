<?php

namespace App\Repository;

use App\Models\CallEvent;

interface ICallEventRepository
{

    public function create(array $data):CallEvent;
}
