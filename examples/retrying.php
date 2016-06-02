<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$driver = new Queue\Driver\MemoryDriver();

$router = new Queue\Router\SimpleRouter('q');
$producer = new Queue\DefaultProducer($driver, $router);

$handler = new Queue\Handler\CallableHandler(function () {
    static $calls = 0;

    if ($calls >= 5) {
        throw new Queue\Exception\SimpleMustStop('stopit');
    }

    $calls++;

    throw new \Exception('broken');
});


$consumer = new Queue\DefaultConsumer(
    $driver,
    $handler,
    new Queue\Retry\LimitedSpec(5), // allow five retries
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));

exit($consumer->run('q'));
