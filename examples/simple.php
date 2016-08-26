<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$driver = new Queue\Driver\MemoryDriver();

$router = new Queue\Router\SimpleRouter('q');
$producer = new Queue\DefaultProducer($driver, $router);

// an example callback handler. If you're doing something like this in your
// own application consider using `pmg/queue-mapping-handler`
$handler = new Queue\Handler\CallableHandler(function (Queue\Message $msg) {
    switch ($msg->getName()) {
        case 'TestMessage':
            // noop
            break;
        case 'TestMessage2':
            throw new \Exception('oops');
            break;
        case 'MustStop':
            throw new Queue\Exception\SimpleMustStop('stopit');
            break;
    }
});
$consumer = new Queue\DefaultConsumer(
    $driver,
    $handler,
    new Queue\Retry\NeverSpec(), // allow never retry messages
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('TestMessage2'));
$producer->send(new Queue\SimpleMessage('MustStop'));

exit($consumer->run('q'));
