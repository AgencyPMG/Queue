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

/**
 * Executes message handlers in the same thread.
 *
 * @since   2.0
 */
final class SimpleExecutor extends AbstractExecutor
{
    /**
     * {@inheritdoc}
     */
    protected function executeInternal(Message $message, callable $handler)
    {
        call_user_func($handler, $message);
        return true;
    }
}
