<?php declare(strict_types=1);

/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Queue;

final class CollectingLogger extends \Psr\Log\AbstractLogger
{
    private $messages = [];

    public function log($level, $msg, array $context=array())
    {
        $this->messages[$level][] = strtr($msg, $this->makeReplacements($context));
    }

    public function getMessages($level=null)
    {
        if (null === $level) {
            return array_merge(...$this->messages);
        }

        return isset($this->messages[$level]) ? $this->messages[$level] : [];
    }


    private function makeReplacements(array $context)
    {
        $rv = [];
        foreach ($context as $name => $replace) {
            $rv[sprintf('{%s}', $name)] = $replace;
        }

        return $rv;
    }
}
