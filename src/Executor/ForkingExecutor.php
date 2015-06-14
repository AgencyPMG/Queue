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

namespace PMG\Queue\Executor;

use PMG\Queue\Message;
use PMG\Queue\HandlerResolver;

/**
 * MessageExecutor implementation that forks after fetching the handler. The
 * child process will then execute the handler.
 *
 * No checks are made here on open resources. In other words, if your handler opens
 * resources, do that at the top of the callback rather in than in the contructor
 * or whatever. Or lazily create resources.
 *
 * Beware that the forking executor is simply allow to throw. If the handler
 * errors the exit code will be greater than 0 and the parent will return `false`
 * to the consumer -- jobs will be retried.
 *
 * This is just a way of saying its really easy to shoot yourself in the foot
 * with this thing. So use with caution.
 *
 * @since   2.0
 */
final class ForkingExecutor extends AbstractExecutor
{
    /**
     * @var PcntlHelper
     */
    private $pcntl;

    public function __construct(HandlerResolver $resolver)
    {
        if (!function_exists('pcntl_fork')) {
            // we throw a non queue exception here because we want to be sure
            // to fatal error and give the user some info on whats up.
            throw new \RuntimeException(sprintf('%s can only be used if the pcntl extension is loaded', __CLASS__));
        }

        parent::__construct($resolver);
        $this->pcntl = new PcntlHelper();
    }

    /**
     * {@inheritdoc}
     */
    public function executeInternal(Message $message, callable $handler)
    {
        $child = $this->pcntl->fork();
        if ($child < 1) {
            $status = 0;
            try {
                call_user_func($handler, $message);
            } catch (\Exception $e) {
                // the default "code" is 0, so at least make
                // sure to exit unsuccessfully if that happens.
                $status = $e->getCode() ?: 1;
            }

            exit($status < 255 ? $status : 255);
        }
        
        $status = $this->pcntl->wait($child);

        return $this->pcntl->getStatus($status) < 1;
    }
}
