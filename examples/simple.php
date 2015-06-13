<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$driver = new Queue\Driver\MemoryDriver();

$router = new Queue\Router\MappingRouter([
    'TestMessage'   => 'q',
    'TestMessage2'  => 'q',
    'MustStop'      => 'q',
]);

$resolver = new Queue\Resolver\SimpleResolver([
    'TestMessage'   => function () {
        // noop
    },
    'TestMessage2'  => function () {
        throw new \Exception('oops');
    },
    'MustStop'      => function () {
        throw new Queue\Exception\SimpleMustStop('stopit');
    },
]);

$producer = new Queue\DefaultProducer($driver, $router);

$consumer = new Queue\DefaultConsumer(
    $driver,
    new Queue\Executor\SimpleExecutor($resolver),
    new Queue\Retry\LimitedSpec(2), // allow two retries
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('TestMessage2'));
$producer->send(new Queue\SimpleMessage('MustStop'));

exit($consumer->run('q'));
