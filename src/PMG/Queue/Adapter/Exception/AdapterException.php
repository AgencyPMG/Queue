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

namespace PMG\Queue\Adapter\Exception;

/**
 * Marker interface for exceptions throw by Adapaters
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
interface AdapaterException extends \PMG\Queue\Exception\QueueException
{
    // empty
}
