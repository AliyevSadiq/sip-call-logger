<?php



namespace App\CommandBus\API\Auth\Commands;

use App\CommandBus\Core\Command;

class RegistrationCommand extends Command
{
    public string $email;
    public string $password;
    public string $name;
}
