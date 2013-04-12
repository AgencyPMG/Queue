<?php

require __DIR__ . '/../vendor/autoload.php';

use PMG\Queue;

class SayHello implements Queue\JobInterface
{
    public function work(array $args=array())
    {
        $fh = fopen('php://stdout', 'w');

        fwrite($fh, "Hello, {$args['name']}" . PHP_EOL);

        fclose($fh);
    }
}

$adapter = new Queue\Adapter\DummyAdapter();

$producer = new Queue\Producer($adapter);

$producer->addJob('say_hello', null, array('name' => 'You'));

$producer->addJob('say_hello', null, array('name' => 'Them'));

$consumer = new Queue\Consumer($adapter);

$consumer->whitelistJob('say_hello', 'SayHello');

$consumer->run();
