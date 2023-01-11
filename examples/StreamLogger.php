<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

use Psr\Log\AbstractLogger;

/**
 * A logger implementation that ignores levels and just spits everything
 * out to a stream.
 *
 * @since   2.0
 */
final class StreamLogger extends AbstractLogger
{
    private $stream;

    public function __construct($stream=null)
    {
        $this->stream = $stream ?: fopen('php://stderr', 'w');
    }

    public function __destruct()
    {
        fclose($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context=[]) : void
    {
        $replace = $this->makeReplacements($context);
        fwrite($this->stream, sprintf(
            '[%s] %s%s',
            $level,
            strtr($message, $replace),
            PHP_EOL
        ));
    }

    /**
     * @param array<string, string> $context
     * @return array<string, string>
     */
    private function makeReplacements(array $context) : array
    {
        $rv = [];
        foreach ($context as $name => $replace) {
            $rv[sprintf('{%s}', $name)] = $replace;
        }

        return $rv;
    }
}
