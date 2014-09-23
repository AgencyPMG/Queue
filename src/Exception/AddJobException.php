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
 * Thrown when there's an error adding the job to a queue.
 *
 * @since   0.1
 * @param   Christopher Davis <chris@pmg.co>
 */
class AddJobException extends \RuntimeException implements QueueException
{
    // empty
}
