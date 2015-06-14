<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$driver = new Queue\Driver\MemoryDriver();

$router = new Queue\Router\SimpleRouter('q');

$resolver = new Queue\Resolver\SimpleResolver(function () {
    static $calls = 0;

    if ($calls >= 5) {
        throw new Queue\Exception\SimpleMustStop('stopit');
    }

    $calls++;

    throw new \Exception('broken');
});

$producer = new Queue\DefaultProducer($driver, $router);

$consumer = new Queue\DefaultConsumer(
    $driver,
    new Queue\Executor\SimpleExecutor($resolver),
    new Queue\Retry\LimitedSpec(5), // allow two retries
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));

exit($consumer->run('q'));
