<?php

class SayHello implements \PMG\Queue\JobInterface
{
    public function work(array $args=array())
    {
        $fh = fopen('php://stdout', 'w');

        fwrite($fh, "Hello, {$args['name']}" . PHP_EOL);

        fclose($fh);
    }
}
