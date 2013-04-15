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
 * Thrown when our adapater can't understand the job body.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class BadJobBodyException extends \RuntimeException implements AdapterException
{
    // empty
}
