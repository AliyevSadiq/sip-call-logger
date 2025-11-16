Installation
============

### Requirements
- Download **docker** and **docker-compose** binaries for initialization
- **make** executable

**Step 1:**
- Executing docker as regular user: **(only for linux)**

**Note:** If your docker executable already running by your user then, you can skip this step.

```shell
sudo groupadd docker
sudo usermod -aG docker ${USER}
su -s ${USER}

# For testing
docker --version
```


**Step 2:**

Create new .env file from .env.example

**Step 3:**

Open a command console, enter your project directory and execute:

```console
$ make init
$ make app-init
```
For running migration:

```console
$ make app-migrate
```

For generation key:

```console
$ make app-generate-key
```

For generation api doc in swagger:

```console
$ make doc-generate
```

Usage
============

Run the call-event queue listener:

```console
$ make call-event-queue
```


## API Documentation

Open the Swagger UI at:

**http://localhost:8886/api/documentation**

## Before using the call-event API

1. Create a new user using the **registration** API.
2. Obtain an authentication token using the **login** API.
3. Testing call-event api

For running other laravel commands:

```console
$ docker-compose run --rm php-cli php artisan <command name>
```
Testing
============

For running tests execute:

```console
$ make app-test
```
RabbitMQ integration
============

**Step 1: Install the RabbitMQ Queue Driver**

Run the following command to install the RabbitMQ queue driver package:
```console
$ docker-compose run --rm php-cli composer require vladimir-yuldashev/laravel-queue-rabbitmq:^14.0
```


**Step 2: Add RabbitMQ Environment Variables**

Add RabbitMQ connection parameters to .env file
```dotenv
    RABBITMQ_HOST=rabbitmq
    RABBITMQ_PORT=5672
    RABBITMQ_VHOST=/
    RABBITMQ_QUEUE=default
    RABBITMQ_USER=
    RABBITMQ_PASS=
```

**Step 3: Set RabbitMQ as the Queue Driver**

Update the queue driver in your .env file:

```dotenv
    QUEUE_CONNECTION=rabbitmq
```


**Step 4: Configure RabbitMQ in queue.php**

Add the RabbitMQ configuration to config/queue.php

```php
    'rabbitmq' => [

            'driver' => 'rabbitmq',
            'hosts' => [
               [
                   'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                   'port' => env('RABBITMQ_PORT', 5672),
                   'user' => env('RABBITMQ_USER', 'guest'),
                   'password' => env('RABBITMQ_PASS', 'guest'),
                   'vhost' => env('RABBITMQ_VHOST', '/'),
               ]
            ],
        ],
```
