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
 * Marker interface for all other exceptions.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
interface QueueException
{
    public function getMessage();
}
