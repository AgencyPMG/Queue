<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

class SomeMessage
{
    public $hello = 'World';
}

$driver = new Queue\Driver\MemoryDriver();

$router = new Queue\Router\SimpleRouter('q');
$producer = new Queue\DefaultProducer($driver, $router);

// an example callback handler. If you're doing something like this in your
// own application consider using `pmg/queue-mapping-handler`
$handler = new Queue\Handler\CallableHandler(function (object $msg) {
    var_dump($msg);
    return true;
});
$consumer = new Queue\DefaultConsumer(
    $driver,
    $handler,
    new Queue\Retry\NeverSpec(), // allow never retry messages
    new StreamLogger()
);

$producer->send(new SomeMessage());

exit($consumer->once('q') ? 0 : 1);
