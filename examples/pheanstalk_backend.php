<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/SayHello.php';

use PMG\Queue;

$adapter = new Queue\Adapter\PheanstalkAdapter();

$producer = new Queue\Producer($adapter);
try {
    $producer->addJob('say_hello', array('name' => 'You'));

    $producer->addJob('say_hello', array('name' => 'Them'));
} catch (\Exception $e) {
    var_dump($e->getPrevious()->getMessage());
    exit(1);
}

$consumer = new Queue\Consumer($adapter);

$consumer->whitelistJob('say_hello', 'SayHello');

$consumer->run();
