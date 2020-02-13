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

namespace PMG\Queue\Router;

use PMG\Queue\Router;

/**
 * A router that always returns the same queue name for every message.
 *
 * @since   2.0
 */
final class SimpleRouter implements Router
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
    public function queueFor(object $message) : ?string
    {
        return $this->queueName;
    }
}
