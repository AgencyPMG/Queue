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

namespace PMG\Queue\Router;

use PMG\Queue\Message;

/**
 * A router that always returns the same queue name for every message.
 *
 * @since   2.0
 */
final class SimpleRouter implements \PMG\Queue\Router
{
    /**
     * @var string
     */
    private $queueName;

    public function __construct($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * {@inheritdoc}
     */
    public function queueFor(Message $message)
    {
        return $this->queueName;
    }
}
