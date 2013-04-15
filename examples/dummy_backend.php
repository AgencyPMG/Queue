<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/SayHello.php';

use PMG\Queue;

$adapter = new Queue\Adapter\DummyAdapter();

$producer = new Queue\Producer($adapter);

$producer->addJob('say_hello', array('name' => 'You'));

$producer->addJob('say_hello', array('name' => 'Them'));

$consumer = new Queue\Consumer($adapter);

$consumer->whitelistJob('say_hello', 'SayHello');

$consumer->run();
