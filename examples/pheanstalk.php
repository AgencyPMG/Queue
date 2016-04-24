<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$conn = new \Pheanstalk\Pheanstalk('localhost');
$tubes = $conn->listTubes();
do {
    $queueName = uniqid('example_');
} while (in_array($queueName, $tubes, true));

// native serializer supports allowed classes in PHP 7+
$allowedClasses = null;
if (PHP_VERSION_ID >= 70000) {
    $allowedClasses = array_merge([
        Queue\SimpleMessage::class,
    ], Queue\Driver\PheanstalkDriver::allowedClasses());
}
$serializer = new Queue\Serializer\SigningSerializer(
    new Queue\Serializer\NativeSerializer($allowedClasses),
    "sshhhh, it's a secret"
);
$driver = new Queue\Driver\PheanstalkDriver($conn, [], $serializer);

$router = new Queue\Router\MappingRouter([
    'TestMessage'   => $queueName,
    'TestMessage2'  => $queueName,
    'MustStop'      => $queueName,
]);

$resolver = new Queue\Resolver\MappingResolver([
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
    new Queue\Retry\NeverSpec(),
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('TestMessage2'));
$producer->send(new Queue\SimpleMessage('MustStop'));

exit($consumer->run($queueName));
