<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$driver = new Queue\Driver\MemoryDriver();

$router = new Queue\Router\SimpleRouter('q');

$resolver = new Queue\Resolver\MappingResolver([
    'TestMessage'   => function () {
        // noop
    },
    'TestMessage2'  => function () {
        throw new \Exception('oops');
    },
    'MustStop'      => function () {
        throw new Queue\Exception\SimpleMustStop('this was a broadcast message');
    },
]);

$producer = new Queue\DefaultProducer($driver, $router);

$consumer = new Queue\DefaultConsumer(
    $driver,
    new Queue\Executor\SimpleExecutor($resolver),
    new Queue\Retry\LimitedSpec(2),
    new StreamLogger()
);

// these two jobs will never be seen, because...
$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('TestMessage2'));

// broadcast messages are given high priority and go first
$producer->broadcast(new Queue\SimpleMessage('MustStop'));

exit($consumer->run('q'));
