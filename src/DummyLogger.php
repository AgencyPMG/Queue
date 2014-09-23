<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) 2013 PMG Worldwide
 *
 * @package     PMGQueue
 * @copyright   2013 PMG Worldwide
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue;

/**
 * A logger that does nothing. Used if you choose not to pass a logger to a
 * consumer.
 *
 * @since   0.1
 */
class DummyLogger extends \Psr\Log\AbstractLogger
{
    /**
     * From \Psr\Log\LoggerInterface
     *
     * {@inheritdoc}
     */
    public function log($level, $message, array $context=array())
    {
        // do nothing.
    }
}
