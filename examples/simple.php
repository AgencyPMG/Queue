<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$router = new Queue\Router\SimpleRouter([
    'TestMessage'   => 'q',
    'TestMessage2'  => 'q',
    'MustStop'      => 'q',
]);

$factory = new Queue\Factory\CachingFactory(new Queue\Factory\MemoryFactory());

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

$producer = new Queue\DefaultProducer($router, $factory);

$consumer = new Queue\DefaultConsumer(
    $factory,
    new Queue\Executor\SimpleExecutor($resolver),
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('TestMessage2'));
$producer->send(new Queue\SimpleMessage('MustStop'));

$consumer->run('q');
