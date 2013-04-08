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

namespace PMG\Queue\Exception;

/**
 * Thrown when something goes wrong in the consumer and it has to exit.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class ConsumerException extends \RuntimeException implements QueueException
{
    // empty
}
