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
 * Thrown when a client throws an exception that we dont' really know how to
 * deal with.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class ClientException extends \RuntimeException implements AdapterException
{
    // empty
}
